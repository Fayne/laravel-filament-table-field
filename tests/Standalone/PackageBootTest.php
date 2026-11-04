<?php

namespace Fayne\TableField\Tests\Standalone;

use Fayne\TableField\TableFieldServiceProvider;
use Orchestra\Testbench\TestCase;

class PackageBootTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [TableFieldServiceProvider::class];
    }

    public function test_package_registers_its_resources_without_a_host_panel(): void
    {
        $this->assertTrue(view()->exists('filament-table-field::table'));
    }
}
