# Relatório Técnico: Correção da Integração Git — `--untracked-files=all`

**Data:** 2026-07-27
**Componente:** `scripts/publish_release.ps1` — Chamadas `git status --porcelain`
**Categoria:** Correção de Defeito de Integração

---

## 1. Defeito Corrigido

O comando `git status --porcelain`, quando utilizado sem o flag `--untracked-files=all`, colapsa diretórios inteiramente não-rastreados em uma única entrada terminada com `/` (ex: `?? .agents/config/`). Esse path colapsado não corresponde a nenhum padrão declarado em `pipeline-categories.json`, causando bloqueio falso-positivo na validação da Working Tree.

### Causa Raiz (Referência)

Documentada integralmente no relatório anterior: `docs/releases/git_status_working_tree_root_cause_report.md`.

---

## 2. Implementação Aplicada

### Alteração Única: Adição do flag `--untracked-files=all`

Aplicada em **três** chamadas no arquivo `scripts/publish_release.ps1`:

| Linha | Bloco Funcional | Antes | Depois |
|---|---|---|---|
| **237** | Validação Working Tree (Etapa 7) | `@("status", "--porcelain")` | `@("status", "--porcelain", "--untracked-files=all")` |
| **470** | Git Add dinâmico (comissionamento) | `@("status", "--porcelain")` | `@("status", "--porcelain", "--untracked-files=all")` |
| **495** | Validação pós-comissionamento | `@("status", "--porcelain")` | `@("status", "--porcelain", "--untracked-files=all")` |

### Componentes NÃO Alterados

- `pipeline-categories.json` — inalterado
- `Convert-GlobToRegex` — inalterada
- `Test-IsFileAllowed` — inalterada
- `prepare_release.ps1` — inalterado
- `build_release.ps1` — inalterado
- Lógica de versionamento, GitHub CLI, geração do ZIP, Release Notes — inalterados

---

## 3. Testes Executados

### 3.1 Validação de Sintaxe PowerShell
```
[PASS] PowerShell syntax is valid for publish_release.ps1
```

### 3.2 Verificação de Chamadas
```
Old calls without --untracked-files=all: 0
New calls with --untracked-files=all: 3
[PASS] All 3 calls correctly updated.
```

### 3.3 Compatibilidade de Saída para Arquivos Modificados/Rastreados
Comparação entre `git status --porcelain` e `git status --porcelain --untracked-files=all` para os 14 arquivos modificados (status `M`):
```
[PASS] Modified/tracked files produce identical output in both modes (14 entries).
```
**Conclusão:** Nenhuma regressão — arquivos já rastreados ou com status `M` produzem exatamente a mesma saída em ambos os modos.

### 3.4 Expansão de Diretórios Não-Rastreados
```
Default mode collapsed dir: '.agents/config/'
[PASS] Default mode collapses directories, --untracked-files=all expands them to individual files.
```
**Conclusão:** Confirmado que o modo default colapsa `.agents/config/` em uma entrada de diretório, enquanto `--untracked-files=all` expande para `.agents/config/pipeline-categories.json`.

### 3.5 Matching de Wildcards Pós-Correção
```
[PASS] '.agents/config/pipeline-categories.json' matches wildcard '.agents/config/*.json'
       (regex: ^\.agents/config/[^/]*\.json$)

[PASS] Collapsed path '.agents/config/' correctly does NOT match any pattern
       (confirms the bug was in git output, not matching logic)
```
**Conclusão:** O path expandido casa corretamente com o padrão do JSON. O path colapsado (com trailing slash) é corretamente rejeitado, confirmando que o defeito residia exclusivamente na camada de integração Git.

---

## 4. Confirmação da Eliminação do Defeito

| Cenário | Antes da Correção | Depois da Correção |
|---|---|---|
| `.agents/config/` (diretório novo) | ❌ BLOQUEIO (colapsado) | ✅ Expandido para `.agents/config/pipeline-categories.json` → ACEITO |
| Arquivos modificados (status `M`) | ✅ Funcionava | ✅ Continua funcionando (sem regressão) |
| Arquivos não-rastreados individuais | ✅ Funcionava | ✅ Continua funcionando (sem regressão) |

---

## 5. Documentação Atualizada

- **`docs/architecture/RELEASE_ARCHITECTURE.md`** — Seção 7 expandida com registro permanente de que a integração Git utiliza `--untracked-files=all` como pré-requisito obrigatório do matching por categorias.

---

## 6. Avaliação de Impacto

| Critério | Resultado |
|---|---|
| Formato de `pipeline-categories.json` | ✅ Inalterado |
| Função `Convert-GlobToRegex` | ✅ Inalterada |
| Função `Test-IsFileAllowed` | ✅ Inalterada |
| `prepare_release.ps1` | ✅ Inalterado |
| `build_release.ps1` | ✅ Inalterado |
| Versionamento e CHANGELOG | ✅ Inalterados |
| GitHub CLI e Release Notes | ✅ Inalterados |
| Validação estrutural do ZIP | ✅ Inalterada |
| Política de segurança | ✅ Inalterada |
| UTF-8 encoding | ✅ Inalterado |
| Arquitetura Configuration over Code | ✅ Inalterada |

---

## Resumo para Release

- **Corrigida** a integração Git da Working Tree: adicionado o flag `--untracked-files=all` às três chamadas `git status --porcelain` do `publish_release.ps1`, eliminando o bloqueio falso-positivo causado por diretórios inteiramente não-rastreados que eram colapsados pelo Git em entradas com trailing slash.
- **Validada** a correção com 5 categorias de testes automatizados confirmando sintaxe, contagem de chamadas, compatibilidade de saída para arquivos rastreados, expansão de diretórios e matching correto de wildcards.
