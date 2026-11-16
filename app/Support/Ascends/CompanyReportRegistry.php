<?php

namespace App\Support\Ascends;

/**
 * Registry of shared reports published under the gsu, ru and uc route prefixes.
 */
final class CompanyReportRegistry
{
    private const COMPANIES = ['gsu', 'ru', 'uc'];

    /**
     * Entries are generated from the shared routes then reviewed manually;
     * company suffixes (-gsu, -ru, -uc) are removed from slugs.
     *
     * @return array<string, CompanyReportDefinition>
     */
    public static function all(): array
    {
        // shortcut: skeleton only, filled by the registry generation task.
        return [];
    }

    public static function get(string $slug): ?CompanyReportDefinition
    {
        return self::all()[$slug] ?? null;
    }

    /**
     * @return list<string>
     */
    public static function companies(): array
    {
        return self::COMPANIES;
    }
}
