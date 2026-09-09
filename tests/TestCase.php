<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Route admin hidup di bawah prefix {locale} (mis. /id/data-jamaah).
     * Helper ini menambahkan prefix tersebut agar test menabrak route yang benar.
     */
    protected function adminUrl(string $path): string
    {
        return '/id'.($path === '/' ? '' : '/'.ltrim($path, '/'));
    }
}