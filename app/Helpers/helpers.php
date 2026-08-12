<?php

use App\Models\Setting;

if (!function_exists('app_data_path')) {
    function app_data_path(string $subpath = ''): string
    {
        $base = getenv('APPDATA');
        if (!$base || $base === false) {
            $base = $_SERVER['APPDATA'] ?? ($_SERVER['HOME'] . '/.pharmcare');
        }
        $path = $base . DIRECTORY_SEPARATOR . 'PharmCare';
        if ($subpath !== '') {
            $path .= DIRECTORY_SEPARATOR . ltrim($subpath, DIRECTORY_SEPARATOR);
        }
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $path;
    }
}

if (!function_exists('is_setup_complete')) {
    function is_setup_complete(): bool
    {
        $marker = app_data_path('.setup_complete');
        return file_exists($marker);
    }
}

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('format_price')) {
    function format_price($amount)
    {
        return number_format((float) ($amount ?? 0), 0);
    }
}

if (!function_exists('feature_enabled')) {
    function feature_enabled(string $feature): bool
    {
        return \App\Services\FeatureService::enabled($feature);
    }
}

if (!function_exists('app_edition')) {
    function app_edition(): string
    {
        return \App\Services\FeatureService::edition();
    }
}

