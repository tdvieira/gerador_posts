# Relatório Técnico: Correção do Mecanismo de Wildcards da Working Tree

**Data:** 2026-07-27
**Componente:** `scripts/publish_release.ps1` — Função `Test-IsFileAllowed`
**Categoria:** Correção de Defeito Arquitetural

---

## 1. Causa Raiz

A implementação anterior da função `Test-IsFileAllowed` utilizava o operador nativo `-like` do PowerShell para realizar o matching entre os caminhos de arquivos reportados por `git status --porcelain` e os padrões declarados em `wildcard_matches` do arquivo `.agents/config/pipeline-categories.json`.

**O problema:** O operador `-like` do PowerShell trata o caractere `*` como um curinga que casa **qualquer caractere, incluindo o separador de diretórios `/`**. Essa semântica é incompatível com o globbing de filesystem padrão (utilizado por `.gitignore`, bash e Git), onde `*` casa apenas dentro de um único nível de diretório.

### Consequências Concretas

| Padrão JSON | Path do Arquivo | Resultado com `-like` | Resultado Esperado |
|---|---|---|---|
| `docs/*.md` | `docs/releases/report.md` | ✅ ACEITO (incorreto) | ❌ REJEITADO |
| `docs/*.md` | `docs/unknown/evil.md` | ✅ ACEITO (incorreto) | ❌ REJEITADO |
| `.agents/config/*.json` | `.agents/config/../../etc/passwd.json` | ✅ ACEITO (falha de segurança) | ❌ REJEITADO |

Essa falha tornava os padrões `docs/releases/*.md` e `docs/architecture/*.md` redundantes (já cobertos por `docs/*.md`) e potencialmente permitia a aceitação de paths em subdiretórios não autorizados.

---

## 2. Solução Implementada

### Nova Função: `Convert-GlobToRegex`

Introduzida uma função auxiliar `Convert-GlobToRegex` que converte padrões glob do JSON em expressões regulares ancoradas com semântica correta de filesystem:

```
Etapa 1: Escape de todos os caracteres especiais de regex ([regex]::Escape)
Etapa 2: Restaurar ** (escapado como \*\*) → .* (qualquer profundidade, cruza /)
Etapa 3: Restaurar * remanescente (escapado como \*) → [^/]* (um nível, sem /)
Etapa 4: Ancorar com ^ e $ para matching completo
```

### Regras de Conversão

| Glob | Regex Gerado | Semântica |
|---|---|---|
| `docs/*.md` | `^docs/[^/]*\.md$` | Arquivos `.md` diretamente em `docs/` |
| `docs/releases/*.md` | `^docs/releases/[^/]*\.md$` | Arquivos `.md` diretamente em `docs/releases/` |
| `.agents/config/*.json` | `^\.agents/config/[^/]*\.json$` | Arquivos `.json` diretamente em `.agents/config/` |
| `includes/updater/**/*.php` | `^includes/updater/.*/[^/]*\.php$` | Arquivos `.php` em qualquer subdiretório de `includes/updater/` |

### Alteração na Função `Test-IsFileAllowed`

O bloco de matching de wildcards foi alterado de:
```powershell
# ANTES (defeituoso)
if ($FilePathNorm -like $wildcard) {
```

Para:
```powershell
# DEPOIS (corrigido)
$regexPattern = Convert-GlobToRegex -GlobPattern $wildcard
if ($FilePathNorm -match $regexPattern) {
```

O comportamento de `exact_matches` permanece inalterado (comparação estrita `$FilePathNorm -eq $exact`).

---

## 3. Testes Executados

### Suite de Validação Automatizada (29 Testes — 29 PASS / 0 FAIL)

#### Conversão Glob → Regex (4 testes)
- `docs/*.md` → `^docs/[^/]*\.md$` ✅
- `.agents/config/*.json` → `^\.agents/config/[^/]*\.json$` ✅
- `scripts/*.ps1` → `^scripts/[^/]*\.ps1$` ✅
- `includes/updater/**/*.php` → `^includes/updater/.*/[^/]*\.php$` ✅

#### Correspondências Exatas (6 testes)
- `README.md`, `CHANGELOG.md`, `readme.txt`, `gerador-posts-gemini.php`, `build/gerador-posts-gemini.zip`, `.gitignore` → todos PASS ✅

#### Wildcards de Nível Único (9 testes)
- `.agents/config/pipeline-categories.json` → matched by `.agents/config/*.json` ✅
- `scripts/publish_release.ps1` → matched by `scripts/*.ps1` ✅
- `docs/RELEASE_CHEATSHEET.md` → matched by `docs/*.md` ✅
- `docs/releases/some_report.md` → matched by `docs/releases/*.md` ✅ (NÃO por `docs/*.md`)
- `docs/architecture/RELEASE_ARCHITECTURE.md` → matched by `docs/architecture/*.md` ✅ (NÃO por `docs/*.md`)

#### Enforcement de Limites de Diretório (2 testes)
- `docs/releases/report.md` corretamente casado por `docs/releases/*.md` e NÃO por `docs/*.md` ✅

#### Testes Negativos — Devem ser rejeitados (6 testes)
- `assets/css/admin.css` → NO MATCH ✅
- `includes/some_controller.php` → NO MATCH ✅
- `vendor/autoload.php` → NO MATCH ✅
- `node_modules/package.json` → NO MATCH ✅
- `docs/unknown/evil.md` → NO MATCH ✅
- `scripts/subdir/nested.ps1` → NO MATCH ✅

#### Globstar (2 testes)
- `includes/updater/UpdateChecker.php` → matched by `includes/updater/*.php` ✅
- `includes/updater/Puc/v5p7/Vcs/GitHubApi.php` → matched by `includes/updater/**/*.php` ✅

---

## 4. Validação de Sintaxe

- ✅ PowerShell Parser confirmou sintaxe válida para `publish_release.ps1` (zero erros).

---

## 5. Documentação Atualizada

- **`docs/architecture/RELEASE_ARCHITECTURE.md`** — Seção 7 expandida com documentação completa da semântica de wildcards (Glob → Regex), incluindo as regras de `*` vs `**` e o mecanismo `Convert-GlobToRegex`.

---

## 6. Confirmação de Não-Regressão

| Componente | Status |
|---|---|
| Formato de `pipeline-categories.json` | ✅ Inalterado |
| Categorias oficiais do JSON | ✅ Inalteradas |
| Lógica de versionamento | ✅ Inalterada |
| `prepare_release.ps1` | ✅ Inalterado |
| `build_release.ps1` | ✅ Inalterado |
| Geração do ZIP | ✅ Inalterada |
| GitHub CLI | ✅ Inalterado |
| Release Notes / CHANGELOG.md | ✅ Inalterados |
| Plugin Update Checker | ✅ Inalterado |
| UTF-8 encoding | ✅ Inalterado |
| Política de segurança | ✅ Inalterada |

---

## Resumo para Release

- **Corrigida** a semântica de matching de wildcards na validação da Working Tree: o operador `-like` do PowerShell (que trata `*` como qualquer caractere incluindo `/`) foi substituído por conversão Glob → Regex com a função `Convert-GlobToRegex`, garantindo que `*` case apenas dentro de um nível de diretório e `**` case qualquer profundidade.
- **Validada** a correção com 29 testes automatizados cobrindo conversão de padrões, correspondências exatas, wildcards de nível único, enforcement de limites de diretório, rejeição de paths não autorizados e globstar.
