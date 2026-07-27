# Changelog

## 2.0.5 - 2026-07-27

### Novidades
- Unificação e automação da arquitetura documental de Release Notes sob o princípio de Single Source of Truth (CHANGELOG.md).
- Oficialização do fluxo operacional do pipeline de deploy em apenas duas etapas ativas (Prepare e Publish).
- Extração dinâmica e consolidação das seções "Resumo para Release" de relatórios técnicos correntes no CHANGELOG.md.
- Sincronização automática entre o CHANGELOG.md local e as notas da GitHub Release remota via --notes-file do GitHub CLI.
- Redirecionamento da ferramenta build_release.ps1 como utilitário técnico complementar e autônomo para manutenção de builds.

## 2.0.4 - 2026-07-27

### Correções
- Correção do erro de interpretação de argumentos do Git (pathspec) no script de deploy através de serialização robusta compatível com CommandLineToArgvW.
- Garantia de que mensagens de commit multilinha e com espaços sejam transmitidas de forma íntegra.

## 2.0.3 - 2026-07-27

### Melhorias
- Substituição da whitelist estática de documentações de release por uma identificação dinâmica baseada em wildcards (docs/releases/*.md).
- Implementação de rastreamento e commit automático para todos os relatórios de release, reduzindo o custo de manutenção futura do pipeline.

## 2.0.2 - 2026-07-27

### Adicionado
- Atualizacao e consolidacao da Release v2.0.2.

## 2.0.1 - 2026-07-26

### Adicionado
- Atualização e consolidação da Release v2.0.1.

## 2.0.0 - 2026-07-24

### Adicionado (Refatoração da Camada de IA)
- **Nova Arquitetura Orientada a Objetos (OOP):** Migração completa de lógicas procedurais para classes robustas sob o namespace `GPG`.
- **Autoloader PSR-4 Nativo:** Implementado o carregador automático no ponto de entrada, mapeando de forma exata todas as classes sob `includes/`.
- **Contratos e Provedores de IA:** Criadas interfaces (`TextProviderInterface`, `ImageProviderInterface`), classe base comum (`AbstractProvider`) e provedores dedicados (`GeminiProvider`, `OpenAIProvider`, `GroqProvider`, `GoogleImagenProvider` e `DallEProvider`).
- **PromptBuilder Dedicado:** Centralizada a construção lógica e estilização dos prompts de texto e imagens, removendo formatação do escopo dos controladores.
- **Serviço de Mídia Desacoplado:** Centralização de downloads seguros contra SSRF, conversões de formato WebP de alta fidelidade e cortes Retina em `MediaProcessor`.
- **Controlador AJAX Isolado:** Criada a classe `AjaxController` para tratar e sanitizar todas as chamadas de interface do plugin, garantindo validações de nonces e capabilities nativas do WordPress.
- **Centralização de Configurações:** Criada a classe `Config` para isolar a gravação e a recuperação transparente de credenciais e opções no banco de dados.

## 1.2.6

### Melhorias
- Corrigida alteração da nomenclatura das APIs nas configurações.
- "Google" → "Google API"
- "OpenAI" → "OpenAI API"

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
