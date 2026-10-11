# Changelog

All notable changes to this project are documented in this file.

## [2026.10] - 2026-10-12

- hepatologia-course is a public Spanish-language medical education demo for a hepatologist, containing a static HTML/CSS/JS course site and two WordPress themes (juliosm-main, juliosm-academia) with prebuilt ZIPs.
- Current state: last functional commit was 2026-04-15 (ZIPs rebuilt to fix a blank homepage); no code changes since.
- Reviewed October 2026: the README live-demo URLs (hepatologia-course.vercel.app and /portal.html) and skynetjoe.com returned HTTP 200.
- Known gaps: Mercado Pago checkout is described as ready for integration, not integrated; the committed ZIPs can drift from their sources; no LICENSE at repo root. Theme Version headers were left at 1.0.0 to match the packaged ZIPs.

## [2026.09] - 2026-09-16

- Maintenance review of hepatologia-course — a Spanish-language medical education platform for Dr. Julio Santiago Marcelo, a Peruvian hepatologist, teaching cirrhosis patient care (3 modules + 3 bonuses).
- Status: public. The repo holds two things: a static HTML/CSS/vanilla-JS demo (landing page, `portal.html` student portal, `como-funciona.html`), which the README says is deployed at https://hepatologia-course.vercel.app; and two WordPress themes, `juliosm-main` (doctor site) and `juliosm-academia` (Tutor LMS academy), both at theme version 1.0.0, plus prebuilt ZIPs of each. Last commit 2026-04-16 rebuilt those ZIPs to fix a blank homepage.
- Reviewed September 2026: docs refreshed, `## Status` line added to README, changelog started, versioned as v2026.09. No theme PHP, static pages or ZIPs were touched, and both theme `Version:` headers were deliberately left at 1.0.0 so they keep matching the packaged ZIPs.
- Known gaps: Mercado Pago checkout is described as "ready for integration", not integrated; Vimeo Pro lessons, quiz scoring and 6-month access expiry live in the static demo rather than a backend; the built ZIPs are committed alongside their sources, so the two can drift; no LICENSE at repo root (the themes themselves declare GPL-2.0-or-later); no CHANGELOG before this release.
- The live demo URL in the README was not re-checked in this pass.
