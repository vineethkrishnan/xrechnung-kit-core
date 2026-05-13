# Contributing to xrechnung-kit

Thanks for considering a contribution. This document covers how to propose changes, the commit convention, the review bar, and how releases are cut.

## Ground rules

- Be kind. The [Code of Conduct](CODE_OF_CONDUCT.md) applies.
- Open an issue before opening a non-trivial PR. We try to scope and gate work in the issue before code lands.
- Security issues do not go in the public tracker. See [SECURITY.md](SECURITY.md).

## Repository layout

```
core/             # vinelabs-de/xrechnung-kit
adapters/{laravel,symfony,cakephp,laminas}/
mappers/{bookings,simple}/
kosit-bundle/
cli/
docs/
examples/
benchmarks/
.github/
```

Sub-packages publish to Packagist independently under the `vineethkrishnan/xrechnung-kit-*` vendor.

## Local development

```bash
git clone https://github.com/vinelabs-de/xrechnung-kit.git
cd xrechnung-kit
composer install
composer test
```

KoSIT Schematron validation is opt-in and requires Java 11+:

```bash
composer kosit
```

The bundle (`vineethkrishnan/xrechnung-kit-kosit-bundle`) installs the validator JAR and pinned scenarios under your local cache directory (`XRECHNUNG_KIT_CACHE_DIR`, then `XDG_CACHE_HOME/xrechnung-kit`, then `~/.cache/xrechnung-kit`).

## Commit convention

We use [Conventional Commits](https://www.conventionalcommits.org/). Format:

```
<type>(<scope>): <subject>

<body>
```

- **Type** (required): `feat`, `fix`, `refactor`, `chore`, `docs`, `style`, `perf`, `test`, `build`, `ci`, `revert`.
- **Scope** (optional, recommended): lowercase with hyphens (`core`, `validator`, `laravel-adapter`, ...).
- **Subject**: imperative, lowercase, no trailing period, under 72 chars.
- **Body**: explain the why, not the what. Soft-wrap; do not hard-wrap lines.

No ticket ID is required. If you want to reference a GitHub issue, add a `Refs: #N` trailer in the body.

`@commitlint/config-conventional` runs in CI on every PR. Failed lint blocks the merge.

Breaking changes:

- Append `!` after the type/scope (e.g., `feat(core)!: rename Mapping to MappingData`), or
- Add a `BREAKING CHANGE:` footer in the body.

`release-please` reads commits to drive the next release version.

## Code conventions

- PHP 8.1+. Use enums, readonly, intersection types where natural.
- No `any` analog: use precise types. PHPStan max + Psalm level 1 must pass.
- No comments that restate code. Comments are reserved for non-obvious WHY.
- No silent transliteration in core. Surface input issues at `MappingData` construction.
- Prefer named constructors over telescoping constructors.
- Booleans start with `is`, `has`, `can`, `should`.
- Tests required for any behaviour change.

## Tests

- Unit tests for new classes.
- Integration tests for any change touching the pipeline.
- Snapshot regressions require explicit reviewer approval; PR template flags it.
- KoSIT-strict must stay green against the fixture corpus.

Run locally:

```bash
composer test         # phpunit
composer stan         # phpstan max
composer psalm        # psalm level 1
composer cs           # php-cs-fixer dry-run
composer cs-fix       # php-cs-fixer apply
composer kosit        # KoSIT Schematron against the corpus (requires bundle + Java)
```

## Pull requests

- Branch from `main`. Keep PRs focused; one concern per PR.
- The PR template asks for: motivation, summary of changes, tests added, KoSIT-strict status.
- All CI checks must be green before merge.
- Squash-merge by default; the squash subject must be a Conventional Commit.

## Release process

Releases are automated:

1. Merging to `main` updates the open `release-please` PR.
2. Maintainer reviews and merges the release PR when ready.
3. Tag, GitHub Release, CHANGELOG.md, and Packagist publication happen automatically.
4. Sigstore signs the release artifacts; CycloneDX SBOM is attached.

KoSIT scenarios pinning policy:

- **Patch**: no scenarios bump. No XML output change.
- **Minor**: scenarios may bump if pass/fail equivalence is preserved.
- **Major**: required for any change to emitted XML or to the public API.

## Asking questions

For general questions, open a [GitHub Discussion](https://github.com/vinelabs-de/xrechnung-kit/discussions). Bug reports go in Issues using the templates.

## License

By contributing, you agree that your contributions will be licensed under the MIT License (see [LICENSE](LICENSE)).
