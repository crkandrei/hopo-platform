<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles if they don't exist
        if (!\App\Models\Role::where('name', 'SUPER_ADMIN')->exists()) {
            \App\Models\Role::create(['name' => 'SUPER_ADMIN', 'display_name' => 'Super Admin']);
        }
        if (!\App\Models\Role::where('name', 'COMPANY_ADMIN')->exists()) {
            \App\Models\Role::create(['name' => 'COMPANY_ADMIN', 'display_name' => 'Company Admin']);
        }
        if (!\App\Models\Role::where('name', 'STAFF')->exists()) {
            \App\Models\Role::create(['name' => 'STAFF', 'display_name' => 'Staff']);
        }
    }
}
