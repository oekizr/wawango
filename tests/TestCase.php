<?php

namespace Tests;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed roles (admin, penyedia_jasa, pemesan) after every RefreshDatabase migration,
     * since registration and RBAC flows depend on roles existing.
     */
    protected $seed = true;

    protected $seeder = RoleSeeder::class;
}
