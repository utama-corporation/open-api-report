<?php

namespace App\Support\Ascends;

use Closure;

/**
 * Declarative definition of one report shared by the GSU, RU and UC company prefixes.
 */
final class CompanyReportDefinition
{
    /**
     * @param  array<string, mixed>  $pdf
     * @param  array<string, mixed>  $filters
     * @param  array<string, string|array{service?: string, view?: string}>  $overrides
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $service,
        public readonly string $view,
        public readonly array $pdf,
        public readonly string|Closure $filename,
        public readonly array $filters = [],
        public readonly array $overrides = [],
    ) {}

    public function serviceFor(string $company): string
    {
        return $this->overrideFor($company)['service'] ?? $this->service;
    }

    public function viewFor(string $company): string
    {
        return $this->overrideFor($company)['view'] ?? $this->view;
    }

    public function filenameFor(array $reportData, string $company): string
    {
        // Company label is always uppercase on the printed report.
        return $this->filename instanceof Closure
            ? ($this->filename)($reportData, strtoupper($company))
            : $this->filename;
    }

    /**
     * A company override is either a service FQCN or a service/view pair.
     *
     * @return array{service?: string, view?: string}
     */
    private function overrideFor(string $company): array
    {
        $override = $this->overrides[strtolower($company)] ?? [];

        return is_string($override) ? ['service' => $override] : $override;
    }
}
