# Changelog

## 1.1.2

- Published on a new single-commit history. The 1.1.1 tag pointed at a commit that is no longer in the repository, so this release replaces it on Packagist.
- README wording tidied.
- The live credentials test no longer prints any part of the client ID or API key.

## 1.1.1

- `collect()` with no filters sends `{"model":{}}`, as the API and the README expect, instead of `{"model":[]}`.
- Added an offline test of the request body `collect()` sends, with and without filters.

## 1.1.0

- Versioning: earlier tags used `v1.0.01`, `v1.0.02` style numbering. From 1.1.0 the tags follow semantic versioning.
- Added GitHub Actions CI on PHP 7.4 to 8.4.
- Added offline tests for request signing and HTTP status to exception mapping.
- Live integration tests now skip when no credentials are configured.
- `HttpClient` transport is now in a protected `send()` method, so it can be replaced in tests. Public API is unchanged.
- Removed debug scripts from `tests/`.
- README: added an unofficial-client notice and fixed missing imports in an example.
