<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $marker = app_data_path('.setup_complete');
        $dir = is_dir($marker) ? $marker : dirname($marker);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        @file_put_contents($marker, date('Y-m-d H:i:s'));
    }
}
