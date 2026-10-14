<?php

namespace Tests\Feature;

use Tests\TestCase;

/** Prevent transport and domain boundaries from drifting during future changes. */
class ModuleArchitectureTest extends TestCase
{
    /**
     * @return void
     */
    public function test_controllers_only_handle_transport(): void
    {
        foreach (glob(app_path('Modules/*/Controllers/*/*.php')) as $file) {
            $source = file_get_contents($file);
            $this->assertDoesNotMatchRegularExpression('/DB::|->validate\\(|::(?:query|create|update|find|where)\\(/', $source, $file);
            $this->assertStringNotContainsString('extends Model', $source, $file);
        }
    }

    /**
     * @return void
     */
    public function test_services_do_not_depend_on_http_requests_or_responses(): void
    {
        foreach (glob(app_path('Modules/*/Services/*.php')) as $file) {
            $source = file_get_contents($file);
            $this->assertStringNotContainsString('Illuminate\\Http\\Request', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/response\\(\\)|return view\\(|return back\\(|->validate\\(|DB::table\\(|DB::raw\\(/', $source, $file);
        }
    }

    /**
     * @return void
     */
    public function test_old_flat_domain_directories_are_removed(): void
    {
        $this->assertSame([], glob(app_path('Models/*.php')));
        $this->assertSame([], glob(app_path('Http/Controllers/Admin/*.php')));
        $this->assertSame([], glob(app_path('Http/Controllers/Api/V1/*.php')));
    }
}
