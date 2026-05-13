# Security Policy

## Supported versions

Until v1.0.0 is tagged, only the `main` branch is supported. After v1.0.0, the latest minor release on each supported major receives fixes.

| Version | Supported          |
|---------|--------------------|
| `main`  | Yes (pre-release)  |
| `0.x`   | No tagged releases yet |

## Reporting a vulnerability

Please do **not** open a public issue for security reports.

Use one of the following private channels:

1. **GitHub private vulnerability reporting** (preferred): open the repository's Security tab and click "Report a vulnerability". This creates a private advisory visible only to maintainers.
2. **Email**: `me@vineethnk.in`. Encrypt with the maintainer's public key if you have it; otherwise plain email is acceptable.

Please include:

- A description of the issue and the impact you observed.
- Steps to reproduce, ideally a minimal failing input or fixture.
- The affected version, commit SHA, or branch.
- Any known mitigations or workarounds.

## What to expect

- **Acknowledgement** within 3 business days.
- **Triage and severity assessment** within 7 days.
- **Fix or mitigation plan** communicated within 14 days for high-severity issues.
- **Embargo period** of up to 90 days from report to public disclosure. The maintainer may request an extension if a coordinated fix across downstream consumers is needed; you will be informed if so.
- **Credit**: with your consent, you will be credited in the security advisory and the release notes. Anonymous reports are also welcome.

## Scope

In scope:

- The `core/` library and any officially published `vineethkrishnan/xrechnung-kit-*` package.
- The CLI (`validate-kosit`, etc.) shipped from this repository.
- Bundled XSDs and templates.

Out of scope:

- Third-party dependencies (report upstream; we will follow advisories and update).
- The KoSIT validator JAR or KoSIT scenarios themselves (report to KoSIT).
- Issues that require an attacker who already controls the host filesystem or PHP process.

## Hardening guidance

If you are integrating this library, see `docs/security.md` (planned) for guidance on input boundaries, file system permissions, and safe handling of attachments.
