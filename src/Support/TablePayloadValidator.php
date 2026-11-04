<?php

namespace Fayne\TableField\Support;

use InvalidArgumentException;

class TablePayloadValidator
{
    protected const ALLOWED_TEXT_ALIGNS = ['left', 'center', 'right'];
    protected const ALLOWED_LINK_TARGETS = ['_self', '_blank'];
    protected const ALLOWED_MARK_TYPES = [
        'bold',
        'italic',
        'underline',
        'textColor',
        'backgroundColor',
        'fontSize',
    ];

    public static function validate(array $payload): void
    {
        $rows = (int) ($payload['rows'] ?? 0);
        $columns = (int) ($payload['columns'] ?? 0);
        $cells = $payload['cells'] ?? null;

        if ($rows < 1 || $columns < 1) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.minimum_dimensions'));
        }

        if (! is_array($cells) || count($cells) !== $rows) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.row_count'));
        }

        foreach ($cells as $row) {
            if (! is_array($row) || count($row) !== $columns) {
                throw new InvalidArgumentException(__('filament-table-field::table.validation.column_count'));
            }
        }

        $coverage = array_fill(0, $rows, array_fill(0, $columns, null));

        for ($rowIndex = 0; $rowIndex < $rows; $rowIndex++) {
            for ($columnIndex = 0; $columnIndex < $columns; $columnIndex++) {
                $cell = $cells[$rowIndex][$columnIndex];

                if (! is_array($cell)) {
                    throw new InvalidArgumentException(__('filament-table-field::table.validation.cell_object'));
                }

                if ((bool) ($cell['hidden'] ?? false)) {
                    continue;
                }

                $rowspan = max(1, (int) ($cell['rowspan'] ?? 1));
                $colspan = max(1, (int) ($cell['colspan'] ?? 1));

                if ($rowIndex + $rowspan > $rows || $columnIndex + $colspan > $columns) {
                    throw new InvalidArgumentException(__('filament-table-field::table.validation.merge_bounds'));
                }

                for ($coveredRow = $rowIndex; $coveredRow < $rowIndex + $rowspan; $coveredRow++) {
                    for ($coveredColumn = $columnIndex; $coveredColumn < $columnIndex + $colspan; $coveredColumn++) {
                        if ($coverage[$coveredRow][$coveredColumn] !== null) {
                            throw new InvalidArgumentException(__('filament-table-field::table.validation.merge_overlap'));
                        }

                        $coverage[$coveredRow][$coveredColumn] = [$rowIndex, $columnIndex];
                    }
                }
            }
        }

        for ($rowIndex = 0; $rowIndex < $rows; $rowIndex++) {
            for ($columnIndex = 0; $columnIndex < $columns; $columnIndex++) {
                $cell = $cells[$rowIndex][$columnIndex];
                $coveredBy = $coverage[$rowIndex][$columnIndex];

                if ((bool) ($cell['hidden'] ?? false)) {
                    static::validateHiddenCell($cell, $coveredBy, $rows, $columns);

                    continue;
                }

                if ($coveredBy === null) {
                    throw new InvalidArgumentException(__('filament-table-field::table.validation.visible_region'));
                }

                if ($coveredBy[0] !== $rowIndex || $coveredBy[1] !== $columnIndex) {
                    throw new InvalidArgumentException(__('filament-table-field::table.validation.covered_hidden'));
                }

                static::validateCellContent($cell['content'] ?? null);
                static::validateCellStyle($cell['cellStyle'] ?? null);
            }
        }
    }

    protected static function validateHiddenCell(array $cell, mixed $coveredBy, int $rows, int $columns): void
    {
        $anchor = $cell['anchor'] ?? null;

        if (
            ! is_array($anchor)
            || count($anchor) !== 2
            || ! is_int($anchor[0])
            || ! is_int($anchor[1])
        ) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.anchor_required'));
        }

        if ($anchor[0] < 0 || $anchor[0] >= $rows || $anchor[1] < 0 || $anchor[1] >= $columns) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.anchor_bounds'));
        }

        if ($coveredBy === null || $coveredBy[0] !== $anchor[0] || $coveredBy[1] !== $anchor[1]) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.anchor_mismatch'));
        }
    }

    protected static function validateCellContent(mixed $content): void
    {
        if (! is_array($content)) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.content_document'));
        }

        if (($content['nodeType'] ?? null) !== 'document') {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.document_root'));
        }

        if (! is_array($content['content'] ?? null)) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.document_content'));
        }

        foreach ($content['content'] as $paragraphNode) {
            static::validateParagraphNode($paragraphNode);
        }
    }

    protected static function validateParagraphNode(mixed $node): void
    {
        if (! is_array($node) || ($node['nodeType'] ?? null) !== 'paragraph') {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.paragraph_nodes'));
        }

        if (! is_array($node['content'] ?? null)) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.paragraph_content'));
        }

        foreach ($node['content'] as $childNode) {
            $childType = is_array($childNode) ? ($childNode['nodeType'] ?? null) : null;

            if ($childType === 'text') {
                static::validateTextNode($childNode);

                continue;
            }

            if ($childType === 'hyperlink') {
                static::validateHyperlinkNode($childNode);

                continue;
            }

            throw new InvalidArgumentException(__('filament-table-field::table.validation.paragraph_children'));
        }
    }

    protected static function validateHyperlinkNode(array $node): void
    {
        $uri = $node['data']['uri'] ?? null;
        $target = $node['data']['target'] ?? '_self';

        if (! is_string($uri) || trim($uri) === '') {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.link_uri'));
        }

        if (! is_string($target) || ! in_array($target, static::ALLOWED_LINK_TARGETS, true)) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.link_target'));
        }

        if (! is_array($node['content'] ?? null)) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.link_content'));
        }

        foreach ($node['content'] as $childNode) {
            if (! is_array($childNode) || ($childNode['nodeType'] ?? null) !== 'text') {
                throw new InvalidArgumentException(__('filament-table-field::table.validation.link_children'));
            }

            static::validateTextNode($childNode);
        }
    }

    protected static function validateTextNode(array $node): void
    {
        if (! array_key_exists('value', $node) || ! is_string($node['value'])) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.text_value'));
        }

        if (! is_array($node['marks'] ?? null)) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.text_marks'));
        }

        foreach ($node['marks'] as $mark) {
            static::validateMark($mark);
        }
    }

    protected static function validateMark(mixed $mark): void
    {
        if (! is_array($mark)) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.mark_object'));
        }

        $type = $mark['type'] ?? null;

        if (! is_string($type) || ! in_array($type, static::ALLOWED_MARK_TYPES, true)) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.mark_type'));
        }

        if ($type === 'textColor' || $type === 'backgroundColor') {
            static::validateColorValue($mark['attrs']['value'] ?? null);
        }

        if ($type === 'fontSize') {
            $fontSize = $mark['attrs']['value'] ?? null;

            if (! is_int($fontSize) || $fontSize < 10 || $fontSize > 48) {
                throw new InvalidArgumentException(__('filament-table-field::table.validation.font_size'));
            }
        }
    }

    protected static function validateCellStyle(mixed $style): void
    {
        if (! is_array($style)) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.cell_style'));
        }

        static::validateColorValue($style['backgroundColor'] ?? null);

        $textAlign = $style['textAlign'] ?? null;

        if ($textAlign !== null && (! is_string($textAlign) || ! in_array($textAlign, static::ALLOWED_TEXT_ALIGNS, true))) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.alignment'));
        }
    }

    protected static function validateColorValue(mixed $value): void
    {
        if ($value === null) {
            return;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.color_string'));
        }

        $normalized = trim($value);

        if (
            ! preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $normalized)
            && ! preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)$/', $normalized)
        ) {
            throw new InvalidArgumentException(__('filament-table-field::table.validation.color_format'));
        }
    }
}
