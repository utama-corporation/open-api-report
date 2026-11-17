<?php

namespace Tests\Unit\Ascends;

use Tests\TestCase;

class CompanyDocsTest extends TestCase
{
    public function test_each_company_doc_matches_shared_doc_count_and_has_no_db_company_name(): void
    {
        $root = base_path('docs/ascends-endpoint');
        $shared = $this->docPaths($root.'/Shared');

        $this->assertNotEmpty($shared);

        foreach (['GSU', 'RU', 'UC'] as $company) {
            $mirrored = array_values(array_filter(
                array_keys($this->docPaths($root.'/'.$company)),
                static fn (string $path): bool => isset($shared[$path])
            ));

            $this->assertCount(count($shared), $mirrored, "{$company} docs do not mirror Shared");

            foreach ($shared as $path => $_) {
                $this->assertArrayHasKey($path, $this->docPaths($root.'/'.$company), "{$company}/{$path} is missing");
            }

            foreach ($this->docPaths($root.'/'.$company) as $path => $file) {
                $this->assertStringNotContainsString('DB_CompanyName', file_get_contents($file), "{$company}/{$path} mentions DB_CompanyName");
            }
        }
    }

    public function test_company_docs_use_company_urls_and_normalized_slugs(): void
    {
        $root = base_path('docs/ascends-endpoint');

        foreach (['gsu' => 'GSU', 'ru' => 'RU', 'uc' => 'UC'] as $code => $label) {
            foreach ($this->docPaths($root.'/'.$label) as $path => $file) {
                $contents = file_get_contents($file);

                $this->assertStringNotContainsString('/internal/ascends/shared/', $contents, "{$label}/{$path} still uses shared URLs");
                $this->assertDoesNotMatchRegularExpression('/-(?:gsu|ru|uc)\/pdf/', $contents, "{$label}/{$path} keeps a company suffix in a URL");
                $this->assertStringNotContainsString('{company}', $contents, "{$label}/{$path} treats company as a parameter");
            }
        }
    }

    public function test_hrm_docs_document_the_dynamic_company_route_name(): void
    {
        foreach (['GSU', 'RU', 'UC'] as $company) {
            $doc = base_path("docs/ascends-endpoint/{$company}/HRM/endpoint-api-shared-hrm.md");

            $this->assertFileExists($doc);
            $this->assertStringContainsString('api.internal.ascends.company.hrm.report.pdf', file_get_contents($doc));
        }
    }

    /**
     * @return array<string, string> relative path => absolute path
     */
    private function docPaths(string $directory): array
    {
        $paths = [];

        if (! is_dir($directory)) {
            return $paths;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            $paths[str_replace('\\', '/', substr($file->getPathname(), strlen($directory) + 1))] = $file->getPathname();
        }

        return $paths;
    }
}
