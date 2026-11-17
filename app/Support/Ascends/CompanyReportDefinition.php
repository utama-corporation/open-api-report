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
     * @param  array<string, string|array{service?: string, view?: string, pdf?: array<string, mixed>, filename?: string|Closure}>  $overrides
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

    /**
     * PDF options are merged so an override may change only the orientation.
     *
     * @return array<string, mixed>
     */
    public function pdfFor(string $company): array
    {
        return array_merge($this->pdf, $this->overrideFor($company)['pdf'] ?? []);
    }

    public function filenameFor(array $reportData, string $company): string
    {
        $filename = $this->overrideFor($company)['filename'] ?? $this->filename;

        // Company label is always uppercase on the printed report.
        return $filename instanceof Closure
            ? $filename($reportData, strtoupper($company))
            : $filename;
    }

    /**
     * A company override is either a service FQCN or a set of per-company values.
     *
     * @return array{service?: string, view?: string, pdf?: array<string, mixed>, filename?: string|Closure}
     */
    private function overrideFor(string $company): array
    {
        $override = $this->overrides[strtolower($company)] ?? [];

        return is_string($override) ? ['service' => $override] : $override;
    }
}
