<?php

namespace Fayne\TableField;

use Illuminate\Support\ServiceProvider;

class TableFieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-table-field');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-table-field');
    }
}
