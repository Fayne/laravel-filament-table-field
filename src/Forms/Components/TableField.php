<?php

namespace Fayne\TableField\Forms\Components;

use Closure;
use Fayne\TableField\Support\TablePayloadNormalizer;
use Fayne\TableField\Support\TablePayloadValidator;
use Filament\Forms\Components\Field;

class TableField extends Field
{
    protected string $view = 'filament-table-field::table';

    protected array $tableOptions = [];

    public function tableOptions(array $options): static
    {
        $this->tableOptions = $options;
        $this->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null) {
                return;
            }
            try {
                $value = is_string($value) ? json_decode($value, true, flags: JSON_THROW_ON_ERROR) : $value;
                if ($value === null) {
                    return;
                }
                if (! is_array($value)) {
                    throw new \InvalidArgumentException(__('filament-table-field::table.invalid_payload'));
                }
                // Bound allocations first; validate the submitted layout without repairing overlapping regions.
                TablePayloadNormalizer::normalize($value, $this->tableOptions);
                TablePayloadValidator::validate($value);
            } catch (\InvalidArgumentException|\JsonException $exception) {
                $fail($exception->getMessage());
            }
        });
        $this->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true, flags: JSON_THROW_ON_ERROR) : $state);

        return $this;
    }

    public function getTableOptions(): array
    {
        return $this->tableOptions;
    }

    public function hydrateState(?array &$hydratedDefaultState, bool $shouldCallHydrationHooks = true, bool $shouldApplyStateCasts = true, array &$appliedStateCastPaths = []): void
    {
        $value = $this->getRawState();
        if (is_string($value)) {
            $value = $value === '' ? null : json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        }
        $this->rawState(json_encode(TablePayloadNormalizer::normalize($value, $this->tableOptions), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        parent::hydrateState($hydratedDefaultState, $shouldCallHydrationHooks, $shouldApplyStateCasts, $appliedStateCastPaths);
    }
}
