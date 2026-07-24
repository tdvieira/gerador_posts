# Changelog

## 1.2.5

### Melhorias
- Alterada a nomenclatura das APIs nas configurações.
- "Google Gemini API" → "Google API"
- "Google Imagen 4" → "Google API"
- "OpenAI DALL-E 3" → "OpenAI (GPT)"

## 1.2.4

- Corrigido...
- Melhorado...

## 1.2.3

- Corrigido...
- Melhorado...

## 1.2.2

- Corrigido...
- Adicionado...
- Melhorado...

## [1.2.1] - 2026-07-24

### Corrigido
- Corrigido o empacotamento do ZIP para compatibilidade com WordPress.
- Atualizado o versionamento para manter consistência entre código, release e distribuição.

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-07-23

### Added
- Native support for multiple Text AI providers: Google Gemini (3.5 Flash, 3.1 Pro/Flash-Lite), OpenAI (GPT-4o/4o-mini, o1-mini), and Groq Cloud (Llama 3.3, 3.1, Gemma 2).
- Image generation integration using OpenAI DALL-E 3, Google Imagen 4, and Flux/Flux-Anime.
- Automated media optimization converting images to WebP format (90% quality) and crop retina resolution (`1408x474px` for featured thumbnails and `1408x792px` for body images).
- Built-in dynamic caching with WordPress Transients API (12-hour duration) for posts linking lists and recommended posts pool.
- Strict Server-Side Request Forgery (SSRF) validation using `wp_http_validate_url()` on all remote media download operations.
- Automated broken link validator (HTTP 404 filtering) during post generation.
- Batch Scheduler tab permitting sequential article creation with a visual execution progress pipeline.

### Changed
- Refactored administrative style and scripts loaders from inline templates into `/assets/css/admin.css` and `/assets/js/admin.js` respectively (Separation of Concerns).
- Decoupled monolithic PHP controllers (`gpg_handle_generate_post` and `gpg_handle_save_post`) into discrete single-responsibility helper functions.
- Restricted HTTP SSL verification bypasses to trigger exclusively under local development environments (`local` and `development` environment types).

### Security
- Hardened AJAX endpoints with mandatory WordPress Nonces validation and Capability checks (`manage_options`).
- Excluded and purged SQL backups, markdown reports, and temporary logs from the web-accessible directory.
