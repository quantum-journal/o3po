# Repository guidance

## Project structure

- This repository is a PHP WordPress plugin. Runtime code is under `o3po/`, with shared classes in `o3po/includes/`; tests are under `tests/`.
- Follow the existing class and file organization. Include dependent PHP classes with `require_once` where the surrounding code does so, and keep reusable domain logic separate from presentation-specific shortcode code.
- Preserve WordPress behavior and conventions when changing routes, post metadata, hooks, templates, or transients. Check all relevant lifecycle paths when changing cached data.

## Validation

- Run `make lint` for PHP syntax checks and `make test` for the PHPUnit suite.
- For focused coverage, run PHPUnit with the repository bootstrap, for example: `phpunit --bootstrap tests/resources/bootstrap.php tests/O3PO_PeopleShortcodesTest.php`.
- `tests/resources/bootstrap.php` supplies WordPress test doubles. Keep test-only instrumentation explained at its declaration and verify that tests exercise the behavior it measures.
- Some publication metadata tests rely on external tools or environment details; inspect failure output before attributing test failures to a code change.

## People and editor pages

- People records are read from settings through `O3PO_People` in `o3po/includes/people.php`. Keep shared data access and name/sort helpers there; shortcode rendering belongs in `O3PO_PeopleShortcodes`.
- Editor handled-paper lists use a per-editor transient in `o3po/includes/editor-pages.php`. Their refresh duration is an independent constant, currently 43,200 seconds; do not couple it to the configurable cited-by refresh setting.
- When changing handled-paper selection or cached values, preserve the corresponding invalidation behavior for publication saves, deletions, status changes, and handling-editor metadata changes.
- Route rewrite refreshes through `O3PO_RewriteRules` in `o3po/includes/class-o3po-rewrite-rules.php`: schedule refreshes and perform a single soft flush on `wp_loaded`, after endpoint and post-type registration.
- Plugin-endpoint 404 recovery is deferred to a subsequent request and rate-limited; keep route detection aligned with configurable endpoint slugs and publication-type routes.
