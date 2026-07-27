# Arquitetura e Princípios de Release
**Pipeline Oficial de Release — Manual de Engenharia e Design**

---

## 🧭 1. Objetivo da Pipeline

O objetivo principal do **Pipeline Oficial de Release** é fornecer um processo de deploy automatizado, seguro, reprodutível e livre de erros manuais para o plugin **Gerador de Posts (IA)**. A esteira garante a consistência das versões e metadados nas distribuições locais do plugin e nas plataformas remotas de hospedagem e atualização (GitHub e ecossistema WordPress).

---

## 🛠️ 2. Fluxo Oficial de Duas Etapas

A esteira de deploy é estruturada em apenas **duas etapas ativas obrigatórias** executadas pelo operador de release:

```
[Etapa 1: Prepare Release] ──> [Etapa 2: Publish Release]
```

1.  **Etapa 1: Prepare Release (`prepare_release.ps1`):** Responsável pela alteração de versão, consistência textual, agregação automatizada de Release Notes no `CHANGELOG.md`, geração e auditoria do arquivo ZIP.
2.  **Etapa 2: Publish Release (`publish_release.ps1`):** Responsável pela auditoria final da Working Tree, comissionamento automático, envio Git, publicação na API do GitHub com notas dinâmicas e upload do ZIP.

*Nota: O script `build_release.ps1` é um utilitário técnico interno invocado exclusivamente pelo `prepare_release.ps1` de forma transparente. Ele não faz parte do fluxo de trabalho direto do operador.*

---

## 📄 3. Estratégia de Single Source of Truth (SSOT) e Release Notes

-   O arquivo **`CHANGELOG.md`** é instituído como a **Single Source of Truth (Fonte Única de Verdade)** de todo o ecossistema de documentação pública da versão.
-   **Coleta Dinâmica:** Durante a preparação da release, o `prepare_release.ps1` varre a pasta `docs/releases/` identificando todos os relatórios técnicos que mencionam a versão correspondente e extrai o conteúdo delimitado pela seção `## Resumo para Release`, unificando os blocos no `CHANGELOG.md` em formato ordenado e desduplicado.
-   **Sincronização com o GitHub:** O `publish_release.ps1` lê o `CHANGELOG.md` local, extrai exclusivamente o bloco da nova versão e o transmite para a API do GitHub, garantindo que as notas remotas da GitHub Release sejam exatamente idênticas ao registro histórico local.

---

## 🔡 4. Garantia Permanente de Codificação UTF-8

Para blindar o pipeline contra a degradação e corrupção de acentos (como em "Melhorias", "Correções") sob o Windows PowerShell 5.1 (que adota cp1252 por padrão) e PowerShell 7+ (que usa UTF-8), o fluxo adota UTF-8 explícito de ponta a ponta:
-   **Leitura e Escrita:** Utiliza-se explicitamente o parâmetro `-Encoding UTF8` no PowerShell e a classe .NET `[System.Text.Encoding]::UTF8` na leitura do `CHANGELOG.md` e escrita de notas temporárias.
-   **Validação Round-Trip:** O script realiza uma validação de ida e volta, relendo o arquivo temporário gerado e comparando-o com o original na memória. Qualquer distorção ou corrupção de caractere detectada cancela o deploy de forma imediata.

---

## 💻 5. Estratégia de subprocessos do GitHub CLI e Git

Toda a execução de comandos externos do Git e GitHub CLI (`gh`) segue uma padronização rígida controlada pela função centralizadora `Execute-ExternalCommand`:
-   **Análise por Exit Code:** As tomadas de decisão são baseadas estritamente nos códigos de retorno (`$LASTEXITCODE`), eliminando qualquer parsing textual instável ou dependência de idioma do console.
-   **Tabela de Tolerância:** O comando `gh release view` possui tolerância explícita ao código de retorno `1` (que indica que a release ainda não existe e o pipeline pode criá-la). Todos os demais comandos abortam imediatamente em caso de código diferente de zero.
-   **Diagnóstico Visível:** Em falhas, o console imprime a mensagem original do StandardError gerada pelo processo externo para auxiliar no diagnóstico rápido pelo desenvolvedor.

---

## 📦 6. Estratégia de Empacotamento e Validação Estrutural do ZIP

-   **Empacotamento Centralizado de Raiz:** A cópia dos arquivos produtivos da raiz para o diretório de build é governada de forma centralizada por uma coleção `$root_files` no script `build_release.ps1`. Essa coleção contém os arquivos oficiais `gerador-posts-gemini.php`, `admin-ui.php`, `LICENSE`, `README.md`, `readme.txt`, `CHANGELOG.md` e `SECURITY.md`. O uso dessa coleção e de um loop síncrono garante que o `readme.txt` de metadados WordPress seja obrigatoriamente incluído no ZIP de release, reduzindo intervenções manuais e eliminando riscos de divergência.
-   **Validação Estrutural:** Nenhum arquivo compactado ZIP é publicado sem aprovação técnica. O script de build executa de forma imediata uma auditoria automatizada do pacote contendo 8 critérios específicos de qualidade exigidos por servidores WordPress:
    -   Presença do arquivo de manifesto principal.
    -   Paridade de versão declarada.
    -   Ausência de arquivos de desenvolvimento (.gitkeep, logs, lixo administrativo).
    -   Conformidade dos separadores `/` nos metadados de caminhos do ZIP.
    Qualquer inconformidade deleta o ZIP e invalida o pipeline.

---

## 🚦 7. Validação da Working Tree por Categorias Arquiteturais (Configuration over Code)

A validação da Working Tree do Git no script de publicação é 100% orientada a dados e baseada no princípio **Configuration over Code**. O script carrega e valida os arquivos modificados de forma dinâmica a partir da especificação externa centralizada no arquivo **`.agents/config/pipeline-categories.json`**.
-   **Estrutura de Arquivo:** O JSON categoriza as permissões entre `exact_matches` (mapeamento estrito de arquivos específicos) e `wildcard_matches` (padrões de pastas e extensões permitidas).
-   **Semântica de Wildcards (Glob → Regex):** Os padrões declarados em `wildcard_matches` são interpretados com **semântica de filesystem globbing**, análoga ao comportamento de `.gitignore` e bash. A função interna `Convert-GlobToRegex` converte cada padrão glob em uma expressão regular ancorada com as seguintes regras:
    -   **`*` (asterisco simples):** Casa qualquer sequência de caracteres **exceto `/`**, restringindo o matching a um único nível de diretório. Exemplo: `docs/*.md` aceita `docs/CHEATSHEET.md` mas rejeita `docs/releases/report.md`.
    -   **`**` (globstar):** Casa qualquer sequência de caracteres **incluindo `/`**, permitindo matching em qualquer profundidade de subdiretórios. Exemplo: `includes/updater/**/*.php` aceita `includes/updater/Puc/v5p7/Vcs/GitHubApi.php`.
    -   **Caracteres especiais de regex** presentes nos padrões (como `.`) são automaticamente escapados antes da conversão, garantindo correspondência literal.
-   **Categorias Reconhecidas:** Engloba documentações oficiais (`docs/releases/`, `docs/architecture/`, `.agents/memory/`), scripts da esteira (`scripts/`), manifestos/bootstrap, subsistemas do updater e configs de infraestrutura.
-   **Integração Git (`--untracked-files=all`):** Todas as chamadas a `git status --porcelain` no script de publicação utilizam permanentemente o flag `--untracked-files=all`. Esse flag impede que o Git colapse diretórios inteiramente não-rastreados em uma única entrada terminada com `/` (ex: `.agents/config/`), forçando a listagem individual de cada arquivo (ex: `.agents/config/pipeline-categories.json`). Essa garantia é pré-requisito obrigatório para o funcionamento correto do matching por wildcards e categorias arquiteturais.
-   **Bloqueios Estritos:** Qualquer modificação em arquivos de código de negócios ativos (controladores, estilos de interface CSS, lógicas JS administrativas, dumps e backups) localizados fora destas categorias provoca a interrupção imediata do deploy, garantindo segurança contra deploys inconsistentes.

---

## 🔒 8. Política de Segurança da Release

O pipeline implementa barreiras administrativas rígidas contra deploys incorretos ou vazamento de dados:
-   **Checagem de Credenciais:** Validação da sessão local do GitHub CLI via `gh auth status` e acessibilidade de escrita no repositório via `gh repo view` antes de iniciar as operações Git.
-   **Integridade Local vs Remota:** Impedimento de publicação caso a tag local já exista no origin remoto para evitar sobrescritas acidentais de versões publicadas.

---

## 📂 9. Responsabilidade de cada Script e Arquivos Oficiais

### Arquivos Oficiais Integrados
-   **`scripts/prepare_release.ps1`:** Script de preparação e controle de metadados.
-   **`scripts/build_release.ps1`:** Script utilitário interno de empacotamento e validação do ZIP.
-   **`scripts/publish_release.ps1`:** Script de publicação, comissionamento e deploy no GitHub.
-   **`includes/updater.php`:** Mecanismo de inicialização do Plugin Update Checker.
-   **`readme.txt`:** Arquivo oficial de metadados para consumo no ecossistema WordPress.
-   **`README.md`:** Documentação técnica voltada para o GitHub.

---

## 📈 10. Princípios Permanentes de Evolução da Pipeline

Qualquer evolução ou refatoração futura do Pipeline Oficial de Release deve, obrigatoriamente, seguir estes princípios de design de software:

1.  **Reduzir Manutenção Manual:** Novas funcionalidades ou arquivos oficiais do repositório devem ser enquadrados automaticamente por caminhos ou padrões funcionais, evitando alterações na esteira de deploy.
2.  **Eliminar Whitelists Estáticas:** Privilegiar a validação por categorias estruturais dinâmicas e o status dinâmico do Git (`git status --porcelain`) para mapeamentos e comissionamentos.
3.  **Eliminar Duplicação de Regras:** Garantir que regras de arquivos aceitos e comissões do Git utilizem as mesmas funções lógicas compartilhadas (ex: `Test-IsFileAllowed`).
4.  **Preservar o Silêncio em Sucesso e Detalhe em Erro:** Manter as interfaces de console limpas e baseadas em ASCII puro em execuções normais, mas fornecer StandardError original completo em falhas.
5.  **Segurança e Compatibilidade de Dependências:** Toda integração com bibliotecas ou componentes de terceiros (como o Plugin Update Checker) deve consumir exclusivamente a API pública oficial suportada pela versão ativa embarcada no plugin. É obrigatória a checagem explícita de compatibilidade (`method_exists`, `class_exists`) para invocações de recursos opcionais ou experimentais de forma a inviabilizar erros fatais de carregamento.
