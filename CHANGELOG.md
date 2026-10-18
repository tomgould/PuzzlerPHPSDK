# Changelog

## 1.1.0

- Versioning: earlier tags used an odd `v1.0.01`, `v1.0.02` style numbering. 1.1.0 returns to normal semantic versioning.
- Added GitHub Actions CI on PHP 7.4 to 8.4.
- Added offline tests for request signing and HTTP status to exception mapping.
- Live integration tests now skip when no credentials are configured.
- `HttpClient` transport is now in a protected `send()` method, so it can be replaced in tests. Public API is unchanged.
- Removed debug scripts from `tests/`.
- README: added an unofficial-client notice and fixed missing imports in an example.
