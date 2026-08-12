<?php

namespace App\Services;

class FeatureService
{
    /**
     * Determine if a feature module is enabled.
     * Usage: Feature::enabled('advanced_reports')
     */
    public static function enabled(string $feature): bool
    {
        return LicenseService::isModuleEnabled($feature);
    }

    /**
     * Get current application edition.
     */
    public static function edition(): string
    {
        return LicenseService::getEdition();
    }

    /**
     * Is application running in Premium mode?
     */
    public static function isPremium(): bool
    {
        return LicenseService::isPremium();
    }
}
