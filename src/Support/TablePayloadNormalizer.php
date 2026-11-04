<?php

namespace Fayne\TableField\Support;

use InvalidArgumentException;

class TablePayloadNormalizer
{
    public const int VERSION = 1;
    public const int HARD_MAX_ROWS = 1000;
    public const int HARD_MAX_COLUMNS = 200;
    public const int HARD_MAX_CELLS = 50000;
    protected const ALLOWED_MARK_TYPES = [
        'bold',
        'italic',
        'underline',
        'textColor',
        'backgroundColor',
        'fontSize',
    ];

    public static function normalize(mixed $payload, array $options = []): ?array
    {
        if ($payload === null) {
            return null;
        }

        $minRows = max(1, (int) ($options['minRows'] ?? 1));
        $minColumns = max(1, (int) ($options['minColumns'] ?? 1));
        $maxRows = static::resolveMax($options['maxRows'] ?? null, static::HARD_MAX_ROWS, $minRows);
        $maxColumns = static::resolveMax($options['maxColumns'] ?? null, static::HARD_MAX_COLUMNS, $minColumns);

        if (! is_array($payload) || $payload === []) {
            return static::emptyTable($minRows, $minColumns);
        }

        if (static::isLegacyMatrix($payload)) {
            return static::normalizeLegacyMatrix($payload, $minRows, $minColumns, $maxRows, $maxColumns);
        }

        return static::normalizeStructuredPayload($payload, $minRows, $minColumns, $maxRows, $maxColumns);
    }

    protected static function resolveMax(mixed $configuredMax, int $hardCeiling, int $min): int
    {
        $max = $configuredMax === null ? $hardCeiling : min($hardCeiling, (int) $configuredMax);

        return max($min, $max);
    }

    protected static function assertWithinBounds(int $rows, int $columns, int $maxRows, int $maxColumns): void
    {
        if ($rows > $maxRows) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.max_rows', ['max' => $maxRows]));
        }

        if ($columns > $maxColumns) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.max_columns', ['max' => $maxColumns]));
        }

        if ($rows * $columns > static::HARD_MAX_CELLS) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.max_cells'));
        }
    }

    protected static function isLegacyMatrix(array $payload): bool
    {
        if ($payload === []) {
            return true;
        }

        if (array_key_exists('version', $payload) || array_key_exists('cells', $payload)) {
            return false;
        }

        foreach ($payload as $row) {
            if (! is_array($row)) {
                return false;
            }
        }

        return true;
    }

    protected static function normalizeLegacyMatrix(array $payload, int $minRows, int $minColumns, int $maxRows, int $maxColumns): array
    {
        $rows = max($minRows, count($payload));
        $columns = $minColumns;

        foreach ($payload as $row) {
            $columns = max($columns, is_array($row) ? count($row) : 0);
        }

        static::assertWithinBounds($rows, $columns, $maxRows, $maxColumns);

        $table = static::emptyTable($rows, $columns);

        foreach ($payload as $rowIndex => $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($row as $columnIndex => $value) {
                if ($rowIndex >= $rows || $columnIndex >= $columns) {
                    continue;
                }

                $table['cells'][$rowIndex][$columnIndex]['content'] = static::normalizeDocument($value);
            }
        }

        return $table;
    }

    protected static function normalizeStructuredPayload(array $payload, int $minRows, int $minColumns, int $maxRows, int $maxColumns): array
    {
        $cells = is_array($payload['cells'] ?? null) ? $payload['cells'] : [];
        $rows = max($minRows, (int) ($payload['rows'] ?? count($cells)));
        $columns = max($minColumns, (int) ($payload['columns'] ?? 0));

        foreach ($cells as $row) {
            if (! is_array($row)) {
                continue;
            }

            $columns = max($columns, count($row));
        }

        static::assertWithinBounds($rows, $columns, $maxRows, $maxColumns);

        $table = static::emptyTable($rows, $columns);

        foreach ($cells as $rowIndex => $row) {
            if (! is_array($row) || $rowIndex >= $rows) {
                continue;
            }

            foreach ($row as $columnIndex => $cell) {
                if ($columnIndex >= $columns) {
                    continue;
                }

                $table['cells'][$rowIndex][$columnIndex] = static::normalizeCell($cell);
            }
        }

        return $table;
    }

    protected static function normalizeCell(mixed $cell): array
    {
        if (! is_array($cell)) {
            return static::visibleCell(static::normalizeDocument($cell));
        }

        $hidden = (bool) ($cell['hidden'] ?? false);

        if ($hidden) {
            return static::hiddenCell(static::normalizeAnchor($cell['anchor'] ?? null));
        }

        $legacyStyle = is_array($cell['style'] ?? null) ? $cell['style'] : null;
        $legacyLink = is_array($cell['link'] ?? null) ? $cell['link'] : null;

        $content = array_key_exists('content', $cell)
            ? static::normalizeDocument($cell['content'])
            : static::createDocumentFromLegacyValue($cell['value'] ?? '', $legacyStyle, $legacyLink);

        return [
            'content' => $content,
            'rowspan' => max(1, (int) ($cell['rowspan'] ?? 1)),
            'colspan' => max(1, (int) ($cell['colspan'] ?? 1)),
            'hidden' => false,
            'anchor' => null,
            'cellStyle' => static::normalizeCellStyle($cell['cellStyle'] ?? $legacyStyle),
        ];
    }

    protected static function normalizeAnchor(mixed $anchor): ?array
    {
        if (! is_array($anchor) || count($anchor) !== 2) {
            return null;
        }

        return [max(0, (int) $anchor[0]), max(0, (int) $anchor[1])];
    }

    protected static function normalizeCellStyle(mixed $style): array
    {
        if (! is_array($style)) {
            return static::defaultCellStyle();
        }

        return [
            'backgroundColor' => static::normalizeOptionalString($style['backgroundColor'] ?? null),
            'textAlign' => static::normalizeOptionalString($style['textAlign'] ?? null),
        ];
    }

    protected static function normalizeDocument(mixed $document): array
    {
        if (is_string($document) || is_numeric($document)) {
            return static::createDocumentFromLegacyValue($document);
        }

        if (! is_array($document)) {
            return static::emptyDocument();
        }

        $nodeType = $document['nodeType'] ?? null;

        if ($nodeType !== 'document') {
            if (array_key_exists('value', $document) || array_key_exists('marks', $document)) {
                return static::emptyDocument([static::normalizeTextNode($document)]);
            }

            return static::emptyDocument();
        }

        $content = [];

        foreach (($document['content'] ?? []) as $node) {
            $content[] = static::normalizeParagraphNode($node);
        }

        if ($content === []) {
            $content[] = static::createParagraphNode();
        }

        return [
            'nodeType' => 'document',
            'data' => [],
            'content' => $content,
        ];
    }

    protected static function normalizeParagraphNode(mixed $node): array
    {
        if (! is_array($node) || ($node['nodeType'] ?? null) !== 'paragraph') {
            return static::createParagraphNode([static::normalizeTextNode($node)]);
        }

        $content = [];

        foreach (($node['content'] ?? []) as $childNode) {
            if (is_array($childNode) && ($childNode['nodeType'] ?? null) === 'hyperlink') {
                $content[] = static::normalizeHyperlinkNode($childNode);

                continue;
            }

            $content[] = static::normalizeTextNode($childNode);
        }

        if ($content === []) {
            $content[] = static::createTextNode('');
        }

        return [
            'nodeType' => 'paragraph',
            'data' => [],
            'content' => $content,
        ];
    }

    protected static function normalizeHyperlinkNode(mixed $node): array
    {
        if (! is_array($node)) {
            return static::createHyperlinkNode('', [static::createTextNode('')]);
        }

        $uri = static::normalizeOptionalString($node['data']['uri'] ?? $node['uri'] ?? $node['url'] ?? null) ?? '';
        $target = static::normalizeLinkTarget($node['data']['target'] ?? $node['target'] ?? null);
        $content = [];

        foreach (($node['content'] ?? []) as $childNode) {
            $content[] = static::normalizeTextNode($childNode);
        }

        if ($content === []) {
            $content[] = static::createTextNode('');
        }

        return static::createHyperlinkNode($uri, $content, $target);
    }

    protected static function normalizeTextNode(mixed $node): array
    {
        if (! is_array($node)) {
            return static::createTextNode(static::normalizeTextValue($node));
        }

        return static::createTextNode(
            static::normalizeTextValue($node['value'] ?? ''),
            static::normalizeMarks($node['marks'] ?? [])
        );
    }

    protected static function normalizeMarks(mixed $marks): array
    {
        if (! is_array($marks)) {
            return [];
        }

        $normalized = [];
        $seen = [];

        foreach ($marks as $mark) {
            $normalizedMark = static::normalizeMark($mark);
            $key = $normalizedMark['type'] . ':' . json_encode($normalizedMark['attrs'] ?? []);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = $normalizedMark;
        }

        return $normalized;
    }

    protected static function normalizeMark(mixed $mark): array
    {
        if (! is_array($mark)) {
            return ['type' => 'bold'];
        }

        $type = static::normalizeOptionalString($mark['type'] ?? null) ?? 'bold';

        if (! in_array($type, static::ALLOWED_MARK_TYPES, true)) {
            $type = 'bold';
        }

        if ($type === 'textColor' || $type === 'backgroundColor') {
            return [
                'type' => $type,
                'attrs' => [
                    'value' => static::normalizeOptionalString($mark['attrs']['value'] ?? $mark['value'] ?? null),
                ],
            ];
        }

        if ($type === 'fontSize') {
            return [
                'type' => 'fontSize',
                'attrs' => [
                    'value' => static::normalizeOptionalInt($mark['attrs']['value'] ?? $mark['value'] ?? null),
                ],
            ];
        }

        return ['type' => $type];
    }

    protected static function createDocumentFromLegacyValue(mixed $value, ?array $legacyStyle = null, ?array $legacyLink = null): array
    {
        $text = static::normalizeTextValue($value);
        $paragraphValues = preg_split('/\R/u', $text) ?: [''];
        $marks = static::marksFromLegacyStyle($legacyStyle);
        $uri = static::normalizeOptionalString($legacyLink['url'] ?? null);
        $target = static::normalizeLegacyLinkTarget($legacyLink);
        $paragraphs = [];

        foreach ($paragraphValues as $paragraphValue) {
            $textNode = static::createTextNode($paragraphValue, $marks);
            $content = $uri === null
                ? [$textNode]
                : [static::createHyperlinkNode($uri, [$textNode], $target)];

            $paragraphs[] = static::createParagraphNode($content);
        }

        return static::emptyDocument($paragraphs);
    }

    protected static function marksFromLegacyStyle(?array $style): array
    {
        if (! is_array($style)) {
            return [];
        }

        $marks = [];

        if (($style['fontWeight'] ?? null) === 'bold') {
            $marks[] = ['type' => 'bold'];
        }

        if (($style['fontStyle'] ?? null) === 'italic') {
            $marks[] = ['type' => 'italic'];
        }

        if (($style['textDecoration'] ?? null) === 'underline') {
            $marks[] = ['type' => 'underline'];
        }

        $textColor = static::normalizeOptionalString($style['textColor'] ?? null);
        if ($textColor !== null) {
            $marks[] = ['type' => 'textColor', 'attrs' => ['value' => $textColor]];
        }

        $backgroundColor = static::normalizeOptionalString($style['backgroundColor'] ?? null);
        if ($backgroundColor !== null) {
            $marks[] = ['type' => 'backgroundColor', 'attrs' => ['value' => $backgroundColor]];
        }

        $fontSize = static::normalizeOptionalInt($style['fontSize'] ?? null);
        if ($fontSize !== null) {
            $marks[] = ['type' => 'fontSize', 'attrs' => ['value' => $fontSize]];
        }

        return static::normalizeMarks($marks);
    }

    protected static function normalizeTextValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    protected static function normalizeOptionalString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    protected static function normalizeOptionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    protected static function normalizeLinkTarget(mixed $value): string
    {
        return $value === '_blank' ? '_blank' : '_self';
    }

    protected static function normalizeLegacyLinkTarget(?array $legacyLink): string
    {
        if (! is_array($legacyLink)) {
            return '_self';
        }

        return ($legacyLink['openInNewTab'] ?? false) ? '_blank' : '_self';
    }

    protected static function defaultCellStyle(): array
    {
        return [
            'backgroundColor' => null,
            'textAlign' => null,
        ];
    }

    protected static function visibleCell(array $content): array
    {
        return [
            'content' => $content,
            'rowspan' => 1,
            'colspan' => 1,
            'hidden' => false,
            'anchor' => null,
            'cellStyle' => static::defaultCellStyle(),
        ];
    }

    protected static function hiddenCell(?array $anchor): array
    {
        return [
            'content' => null,
            'rowspan' => 1,
            'colspan' => 1,
            'hidden' => true,
            'anchor' => $anchor,
            'cellStyle' => null,
        ];
    }

    protected static function emptyTable(int $rows, int $columns): array
    {
        $cells = [];

        for ($rowIndex = 0; $rowIndex < $rows; $rowIndex++) {
            $cells[$rowIndex] = [];

            for ($columnIndex = 0; $columnIndex < $columns; $columnIndex++) {
                $cells[$rowIndex][$columnIndex] = static::visibleCell(static::emptyDocument());
            }
        }

        return [
            'version' => static::VERSION,
            'rows' => $rows,
            'columns' => $columns,
            'cells' => $cells,
        ];
    }

    protected static function emptyDocument(array $paragraphs = []): array
    {
        return [
            'nodeType' => 'document',
            'data' => [],
            'content' => $paragraphs === [] ? [static::createParagraphNode()] : $paragraphs,
        ];
    }

    protected static function createParagraphNode(array $content = []): array
    {
        return [
            'nodeType' => 'paragraph',
            'data' => [],
            'content' => $content === [] ? [static::createTextNode('')] : $content,
        ];
    }

    protected static function createTextNode(string $value, array $marks = []): array
    {
        return [
            'nodeType' => 'text',
            'value' => $value,
            'marks' => $marks,
            'data' => [],
        ];
    }

    protected static function createHyperlinkNode(string $uri, array $content, string $target = '_self'): array
    {
        return [
            'nodeType' => 'hyperlink',
            'data' => [
                'uri' => $uri,
                'target' => static::normalizeLinkTarget($target),
            ],
            'content' => $content,
        ];
    }
}
