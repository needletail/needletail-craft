# Needletail for Craft Changelog

## Unreleased
### Added
- “All URL resources” bucket type to index all public URL-bearing content (including Assets), with optional support for other plugin element types that have URIs.

### Changed
- Bucket indexing now processes in batches (faster than single-item batches).

### Fixed
- Bucket save handling now persists false/empty values correctly.
- Event-driven indexing now supports buckets that cover multiple element types.
- Translation domain typos (`feed-me` → `needletail`) and general cleanup of unused scaffolding.

## 0.1.0 - 2019-06-20
### Added
- First test release
