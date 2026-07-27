# Relatório de Investigação: Causa Raiz do Bloqueio da Working Tree

**Data:** 2026-07-27
**Modo:** Investigação (Read-Only)
**Componente:** `scripts/publish_release.ps1` — Etapa 7 (Validação da Working Tree)
**Categoria:** Defeito de integração Git

---

## 1. Causa Raiz Confirmada

A causa raiz do bloqueio da Working Tree é o uso do comando `git status --porcelain` **sem o flag `--untracked-files=all`** nas três invocações presentes no script `publish_release.ps1`.

### Comportamento do Git

O comando `git status --porcelain` possui dois modos de listagem de arquivos não-rastreados (untracked):

| Modo | Saída para diretório novo | Saída por arquivo |
|---|---|---|
| `git status --porcelain` (default) | `?? .agents/config/` | N/A (colapsa o diretório) |
| `git status --porcelain --untracked-files=all` | N/A | `?? .agents/config/pipeline-categories.json` |

**Por padrão**, o Git agrupa diretórios inteiramente não-rastreados em uma única entrada terminada com `/` (ex: `.agents/config/`). Isso é uma otimização nativa do Git para manter a saída compacta — quando **nenhum** arquivo de um diretório está rastreado, o Git reporta apenas o nome do diretório em vez de listar cada arquivo individualmente.

Com o flag `--untracked-files=all`, o Git lista **cada arquivo individualmente**, mesmo quando todo o diretório é novo e não-rastreado.

### Evidência Empírica (Reproduzida em Ambiente Real)

**Saída real de `git status --porcelain`:**
```
?? .agents/config/
```

**Saída real de `git status --porcelain --untracked-files=all`:**
```
?? .agents/config/pipeline-categories.json
```

---

## 2. Trecho Exato do Código Responsável

### Ponto de Falha Primário — Linha 237

```powershell
# 7. Validar a working tree (arquivos permitidos de release vs arquivos soltos de desenvolvimento)
$status = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain") -CaptureOutput $true
```

O array de argumentos `@("status", "--porcelain")` NÃO inclui `"--untracked-files=all"`.

### Pontos de Falha Secundários — Linhas 470 e 495

```powershell
# Linha 470: Captura para git add dinâmico
$status_raw = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain") -CaptureOutput $true

# Linha 495: Validação pós-comissionamento
$post_status = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain") -CaptureOutput $true
```

Ambas utilizam a mesma invocação sem `--untracked-files=all`.

---

## 3. Cadeia Completa de Propagação da Falha

```
git status --porcelain
       │
       ▼
Saída: "?? .agents/config/"     ◄── Git colapsa o diretório
       │
       ▼
$line.Substring(3).Trim()
       │
       ▼
$file_path = ".agents/config/"  ◄── Trailing slash preservado
       │
       ▼
$file_path_norm = ".agents/config/"
       │
       ▼
Test-IsFileAllowed -FilePathNorm ".agents/config/"
       │
       ├── exact_matches: ".agents/config/" ≠ (nenhuma entrada)  → MISS
       │
       ├── wildcard_matches: ".agents/config/*.json"
       │   Convert-GlobToRegex → "^\.agents/config/[^/]*\.json$"
       │   ".agents/config/" -match "^\.agents/config/[^/]*\.json$"  → FALSE
       │                                                                 (trailing slash sem nome de arquivo)
       │
       ▼
return $false  →  BLOQUEIO
```

**O path `.agents/config/` não casa com nenhuma regra** porque:
1. Não é um `exact_match` (nenhuma entrada termina com `/`).
2. Não casa com `.agents/config/*.json` porque o regex `^\.agents/config/[^/]*\.json$` exige um nome de arquivo terminado em `.json` após a última `/`, e o path `.agents/config/` não possui nome de arquivo — apenas um trailing slash.

---

## 4. Impacto

### Impacto Direto
- **Bloqueio total da publicação de release** quando qualquer diretório inteiramente novo e não-rastreado estiver na Working Tree.
- O diretório `.agents/config/` (criado durante a sessão anterior para hospedar `pipeline-categories.json`) é o caso real que desencadeou o bloqueio.

### Impacto nos Três Blocos do Script
| Bloco | Linha | Consequência |
|---|---|---|
| **Validação Working Tree** (Etapa 7) | 237 | ❌ Interrompe a release com erro de "alterações pendentes de desenvolvimento" |
| **Git Add dinâmico** (comissionamento) | 470 | ❌ Falha silenciosa — o diretório não é reconhecido como permitido, portanto NÃO é adicionado ao `git add` |
| **Validação pós-comissionamento** | 495 | ❌ Mesmo que a etapa 7 fosse removida, a etapa pós-comissionamento também bloquearia |

### Impacto na Arquitetura
- A arquitetura de categorias (`pipeline-categories.json`) está **correta**. Os padrões `exact_matches` e `wildcard_matches` são adequados.
- O algoritmo `Convert-GlobToRegex` está **correto**. A conversão de `*` para `[^/]*` e `**` para `.*` é semanticamente válida.
- A falha está **exclusivamente** na camada de integração com o Git — o script recebe paths em formato de diretório quando esperava paths de arquivo.

---

## 5. Alternativa de Correção Recomendada

### Correção Mínima e Cirúrgica

Adicionar `"--untracked-files=all"` ao array de argumentos nas **três** chamadas `git status --porcelain`:

**Linha 237:**
```powershell
# ANTES:
$status = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain") -CaptureOutput $true

# DEPOIS:
$status = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain", "--untracked-files=all") -CaptureOutput $true
```

**Linha 470:**
```powershell
# ANTES:
$status_raw = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain") -CaptureOutput $true

# DEPOIS:
$status_raw = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain", "--untracked-files=all") -CaptureOutput $true
```

**Linha 495:**
```powershell
# ANTES:
$post_status = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain") -CaptureOutput $true

# DEPOIS:
$post_status = Execute-ExternalCommand -Command "git" -Arguments @("status", "--porcelain", "--untracked-files=all") -CaptureOutput $true
```

### Por Que Esta Correção É Suficiente

1. O flag `--untracked-files=all` força o Git a listar cada arquivo individualmente, mesmo dentro de diretórios inteiramente novos.
2. Com paths individuais (ex: `.agents/config/pipeline-categories.json`), o matching do `Convert-GlobToRegex` funciona corretamente.
3. Nenhum outro componente do script transforma paths — o `$line.Substring(3).Trim()` e o `.Replace("\", "/")` são passthrough transparentes.

---

## 6. Avaliação de Risco da Alteração

| Critério | Avaliação |
|---|---|
| **Escopo da mudança** | Mínimo — apenas adição de um argumento em 3 chamadas idênticas |
| **Risco de regressão** | Muito baixo — `--untracked-files=all` não altera o formato de saída para arquivos já rastreados ou modificados; apenas desagrega a representação de diretórios não-rastreados |
| **Compatibilidade Git** | Total — o flag `--untracked-files=all` é suportado em todas as versões do Git ≥ 1.7 (2010+) |
| **Impacto em Performance** | Negligenciável — em repositórios pequenos como este plugin, a diferença é imperceptível |
| **Impacto em exatas/wildcards** | Nenhum — arquivos modificados (`M`) já são listados individualmente em ambos os modos |
| **Necessidade de alterar `pipeline-categories.json`** | Nenhuma — o JSON está correto |
| **Necessidade de alterar `Convert-GlobToRegex`** | Nenhuma — o algoritmo está correto |
| **Necessidade de alterar `Test-IsFileAllowed`** | Nenhuma — a função está correta |

---

## 7. Problemas Adicionais Identificados

**Nenhum.** A investigação analisou toda a cadeia de execução (invocação Git → captura de saída → split de linhas → extração de path → normalização → chamada de Test-IsFileAllowed) e confirmou que o **único** ponto de defeito é a ausência do flag `--untracked-files=all` nas chamadas a `git status --porcelain`.

A arquitetura Configuration over Code, o formato do `pipeline-categories.json`, as categorias oficiais, o algoritmo `Convert-GlobToRegex` e a lógica de `Test-IsFileAllowed` estão todos íntegros e funcionam corretamente quando recebem paths de arquivo individuais.

---

## 8. Recomendação Final

A **próxima etapa recomendada é exclusivamente a substituição das três chamadas ao Git**, adicionando `"--untracked-files=all"` como argumento adicional. Nenhuma outra alteração é necessária em qualquer outro componente do pipeline.
