# Ascend Company Reports Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add stable `/internal/ascends/gsu|ru|uc/...` endpoints for every Ascend shared report while leaving the existing shared and company endpoints untouched.

**Architecture:** A declarative registry maps each shared report slug to its service, view, PDF options, filename pattern, and optional per-company service overrides. A single generic controller resolves the company from the route prefix, injects it internally into the service filters, and renders the existing shared view. Routes are generated from the registry in a separate route file required at the end of `routes/api.php`, so existing routes keep precedence.

**Tech Stack:** Laravel 12, PHP ^8.2, Mockery, PHPUnit, Laravel route registration, existing `PdfGenerator`.

**Spec:** `docs/superpowers/specs/2026-11-16-ascends-company-routes-design.md`

## Global Constraints

- Route prefixes exactly `gsu`, `ru`, `uc`; label uppercase `GSU`, `RU`, `UC`.
- New endpoints never read `DB_CompanyName` from the client; XML payload only.
- Internally, inject `company` and `DB_CompanyName` derived from the URL into service filters.
- Existing `/internal/ascends/shared/...` endpoints remain unchanged.
- Existing ~30 `/internal/ascends/{gsu|ru|uc}/...` endpoints remain authoritative for their URLs; new routes must not override them.
- Company-suffixed shared report names lose the suffix; route prefix is the only company marker.
- Follow existing conventions: `PascalCase.php` classes, `kebab-case` URL slugs, `snake_case` view names/route names, concise PHPDoc + one-line "why" comments.
- Never modify `app/Services/Ascends/Shared/GeneralLedger/TrialBalanceMonthly/ArusKasUcReportService.php` (unrelated in-progress work).

## Review Focus

- **Existing company URL collision:** old route must win for `/internal/ascends/ru/hrm/list-karyawan/pdf`; test in Task 4 asserts the old controller still serves it.
- **Client sends wrong/any `DB_CompanyName`:** ignored; test in Task 3 posts a conflicting value and asserts service receives company from URL.
- **Client omits `DB_CompanyName`:** works with XML only; test in Task 3 posts only `xml`.
- **Company-suffixed slug normalization:** `umur-piutang-ru` → `/ru/.../umur-piutang/pdf`, never `/gsu/...`; test in Task 2 and Task 4.
- **Dynamic HRM `{report}` route:** new `/{company}/hrm/{report}/pdf` keeps the shared `whereIn` list; test in Task 4 asserts allowed and rejected slugs.

---

### Task 1: Definition and registry skeleton

**Files:**
- Create: `app/Support/Ascends/CompanyReportDefinition.php`
- Create: `app/Support/Ascends/CompanyReportRegistry.php`
- Test: `tests/Unit/Ascends/CompanyReportRegistryTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces: `CompanyReportDefinition` with constructor `(string $slug, string $service, string $view, array $pdf, string|\Closure $filename, array $filters = [], array $overrides = [])` and methods `serviceFor(string $company): string`, `viewFor(string $company): string`, `filenameFor(array $reportData, string $company): string`.
- Produces: `CompanyReportRegistry::all(): array<string, CompanyReportDefinition>`, `::get(string $slug): ?CompanyReportDefinition`, `::companies(): array` returning `['gsu','ru','uc']`.

- [ ] **Step 1: Write the failing test**

```php
public function test_registry_resolves_service_view_and_filename_for_company(): void
{
    $def = new CompanyReportDefinition(
        slug: 'finance.umur-piutang',
        service: UmurPiutangRuReportService::class,
        view: 'ascends.shared.finance.receivable-summary.umur_piutang_ru.pdf',
        pdf: ['format' => 'A4', 'orientation' => 'portrait', 'column_count' => 5],
        filename: fn (array $data, string $company): string => 'Laporan Umur Piutang ('.$company.').pdf',
        overrides: ['ru' => UmurPiutangRuReportService::class],
    );

    $this->assertSame(UmurPiutangRuReportService::class, $def->serviceFor('gsu'));
    $this->assertSame(UmurPiutangRuReportService::class, $def->serviceFor('ru'));
    $this->assertSame('Laporan Umur Piutang (RU).pdf', $def->filenameFor([], 'RU'));
    $this->assertSame(['gsu','ru','uc'], CompanyReportRegistry::companies());
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Ascends/CompanyReportRegistryTest.php`
Expected: FAIL — classes not found.

- [ ] **Step 3: Implement the value object and skeleton registry**

Implement exact signatures above. Include one-line comments: registry entries are generated-then-reviewed; company suffix is removed from slugs.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Ascends/CompanyReportRegistryTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Support/Ascends tests/Unit/Ascends/CompanyReportRegistryTest.php
git commit -m "feat(ascends): add company report registry skeleton"
```

---

### Task 2: Generate the full registry

**Files:**
- Create: `scripts/ascends/generate-company-report-registry.php`
- Modify: `app/Support/Ascends/CompanyReportRegistry.php`
- Test: `tests/Unit/Ascends/CompanyReportRegistryTest.php`

**Interfaces:**
- Consumes: Task 1 `CompanyReportDefinition`.
- Produces: `CompanyReportRegistry::all()` containing one entry per normalized shared report slug; `allowedCompanies(string $slug): array` returning only company codes the slug is published to.

- [ ] **Step 1: Write the failing coverage test**

```php
public function test_registry_covers_every_shared_route_after_suffix_normalization(): void
{
    $paths = $this->sharedRoutePaths(); // parse routes/api.php in test
    $expected = count($paths);

    $this->assertGreaterThanOrEqual($expected - 1, count(CompanyReportRegistry::all()));
    $this->assertNotNull(CompanyReportRegistry::get('general_ledger.trial_balance_monthly.arus-kas'));
    $this->assertSame(['ru'], CompanyReportRegistry::allowedCompanies('finance.receivable-summary.umur-piutang'));
    $this->assertNull(CompanyReportRegistry::get('finance.receivable-summary.umur-piutang-ru'));
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Ascends/CompanyReportRegistryTest.php`
Expected: FAIL — registry has only skeleton entries.

- [ ] **Step 3: Implement generator and write reviewed registry**

Generator parses shared `Route::post` blocks, resolves controller method bodies to service/view/pdf/filename, strips company suffixes from slugs, and records service overrides for families with existing per-company services. Commit both generator and generated registry.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Ascends/CompanyReportRegistryTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add scripts/ascends app/Support/Ascends/CompanyReportRegistry.php tests/Unit/Ascends/CompanyReportRegistryTest.php
git commit -m "feat(ascends): generate full company report registry"
```

---

### Task 3: Generic company controller

**Files:**
- Create: `app/Http/Controllers/Ascends/CompanyReportController.php`
- Test: `tests/Feature/Ascends/CompanyReportControllerTest.php`

**Interfaces:**
- Consumes: `CompanyReportRegistry::get()`, `GenerateAscendsEmployeeListReportRequest::xmlPayload()`, `XmlSourceLabel()`, `PdfGenerator::render()`.
- Produces: `CompanyReportController::pdf(GenerateAscendsEmployeeListReportRequest $request, string $company, string $report)` returning a PDF response.

- [ ] **Step 1: Write failing tests for company source and filters**

```php
public function test_uses_company_from_route_and_ignores_client_db_company_name(): void
{
    // mock service, expect filters contain company => RU and DB_CompanyName => RU
    // post /api/internal/ascends/ru/... with xml only + DB_CompanyName => GSU
    // assert service called with injected RU
}

public function test_works_without_db_company_name(): void
{
    // post only xml, assert 200
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/Ascends/CompanyReportControllerTest.php`
Expected: FAIL — controller missing.

- [ ] **Step 3: Implement the controller**

Implement exact signature. Steps: resolve definition, merge request input minus `DB_CompanyName`, inject company keys, call service, set `reportData['company']` and `printed_by`, render view with `company` and PDF options, return PDF with registry filename. Add PHPDoc explaining company comes from route and client `DB_CompanyName` is ignored.

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/Ascends/CompanyReportControllerTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Ascends tests/Feature/Ascends/CompanyReportControllerTest.php
git commit -m "feat(ascends): add generic company report controller"
```

---

### Task 4: Route registration

**Files:**
- Create: `routes/ascends-company.php`
- Modify: `routes/api.php` (append `require __DIR__.'/ascends-company.php';`)
- Test: `tests/Feature/Ascends/CompanyReportRoutesTest.php`

**Interfaces:**
- Consumes: `CompanyReportRegistry::all()`, `allowedCompanies()`.
- Produces: named routes `api.internal.ascends.{company}.{slug}.pdf`.

- [ ] **Step 1: Write failing route tests**

```php
public function test_generates_routes_for_all_companies(): void
{
    // assert Route::has for a generic slug under gsu, ru, uc
    // assert suffix-normalized slug only under ru
}

public function test_existing_company_route_still_wins(): void
{
    // get route URL /internal/ascends/ru/hrm/list-karyawan/pdf
    // assert action is old AscendXmlTestController method
}

public function test_dynamic_hrm_route_keeps_where_in(): void
{
    // allowed slug resolves, rejected slug 404
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/Ascends/CompanyReportRoutesTest.php`
Expected: FAIL — routes missing.

- [ ] **Step 3: Implement route generation**

In `routes/ascends-company.php`, loop registry and generate routes only for allowed companies, skip paths already registered in `routes/api.php`, and add the dynamic HRM route with the shared `whereIn` list. Add a one-line comment explaining precedence.

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/Ascends/CompanyReportRoutesTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add routes/ascends-company.php routes/api.php tests/Feature/Ascends/CompanyReportRoutesTest.php
git commit -m "feat(ascends): register company report routes"
```

---

### Task 5: Documentation per company

**Files:**
- Create: `docs/ascends-endpoint/GSU/**`, `docs/ascends-endpoint/RU/**`, `docs/ascends-endpoint/UC/**`
- Test: `tests/Unit/Ascends/CompanyDocsTest.php`

**Interfaces:**
- Consumes: `docs/ascends-endpoint/Shared/**` and `CompanyReportRegistry`.
- Produces: mirrored docs with URL prefix replaced and `DB_CompanyName` removed from parameter tables/curl examples.

- [ ] **Step 1: Write failing docs verification**

```php
public function test_each_company_doc_matches_shared_doc_count_and_has_no_db_company_name(): void
{
    // count shared docs = 46; assert each company dir has same count
    // assert no file contains DB_CompanyName
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Ascends/CompanyDocsTest.php`
Expected: FAIL — dirs missing.

- [ ] **Step 3: Generate docs**

Copy 46 shared docs to each company folder, replace `/internal/ascends/shared/` with `/internal/ascends/{company}/`, strip `DB_CompanyName` rows and curl flags, add note that company comes from route prefix.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Ascends/CompanyDocsTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add docs/ascends-endpoint/GSU docs/ascends-endpoint/RU docs/ascends-endpoint/UC tests/Unit/Ascends/CompanyDocsTest.php
git commit -m "docs(ascends): add company endpoint documentation"
```

---

### Task 6: Full verification and audits

**Files:**
- No new production files.
- Test: full suite.

**Interfaces:**
- Consumes: all previous tasks.

- [ ] **Step 1: Run formatter and syntax check**

Run: `./vendor/bin/pint` then `php -l` on new PHP files.
Expected: PASS.

- [ ] **Step 2: Run full test suite**

Run: `php artisan test`
Expected: PASS.

- [ ] **Step 3: Run project audits**

Run: `php artisan reports:audit-conventions && php artisan reports:audit-api`
Expected: no new issues from this change.

- [ ] **Step 4: Spot-check three endpoints manually**

Run the representative endpoints from Review Focus with the supplied XML and confirm PDF output headers show `GSU`, `RU`, `UC` respectively.

- [ ] **Step 5: Commit verification fixes if any**

```bash
git add -A
git commit -m "chore(ascends): verification fixes for company routes"
```
