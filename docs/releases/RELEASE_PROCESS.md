# Processo de Release (Release Process Manual) — v2.0.3

Este manual descreve o procedimento operacional padrão para geração, validação e publicação de novas versões do plugin **Gerador de Posts (IA)**. Ele estabelece os critérios de segurança e governança para empacotamento da distribuição.

---

## 📖 Índice

1. [Visão Geral do Fluxo de Release](#-visão-geral-do-fluxo-de-release)
2. [Pipeline Oficial de Release](#-pipeline-oficial-de-release)
3. [Repository Bootstrap e Governança](#-repository-bootstrap-e-governança)
4. [Distribution Validation (Validação de Distribuição)](#-distribution-validation-validação-de-distribuição)
5. [Procedimento de Git e Tagging Semântico](#-procedimento-de-git-e-tagging-semântico)
6. [Empacotamento e Upload Manual (GitHub)](#-empacotamento-e-upload-manual-github)
7. [Matriz de Decisão de Release (GO / NO-GO)](#-matriz-de-decisão-de-release-go--no-go)

---

## 🚀 Visão Geral do Fluxo de Release

O fluxo de publicação de uma nova versão do plugin segue uma trilha linear rigorosa baseada em validações automáticas e manuais:

```mermaid
graph TD
    A[Homologação Concluída] --> B[Passo 1: prepare_release.ps1 - Valida e Sincroniza Versão]
    B --> C[Passo 2: build_release.ps1 - Executado Automaticamente - ZIP]
    C --> D[Validação Prática de Instalação e Ativação no WP]
    D --> E[Passo 3: publish_release.ps1 - Publicação Remota - Próxima Etapa]
```

---

## 🛠️ Pipeline Oficial de Release

O processo de empacotamento e publicação do plugin é estruturado no **Pipeline Oficial de Release**, composto por duas etapas operacionais ativas sob o princípio da responsabilidade única (SRP), garantindo reprodutibilidade completa, auditável e automatizada:

| Etapa Operacional | Script Correspondente | Responsabilidade Principal |
| :--- | :--- | :--- |
| **Etapa 1: Prepare Release** | `prepare_release.ps1` | Sincronização de versão, consolidação automática das Release Notes dos relatórios em `docs/releases/*.md` para o `CHANGELOG.md` sem textos fixos, geração do pacote ZIP e validação estrutural do WordPress |
| **Etapa 2: Publish Release** | `publish_release.ps1` | Auditoria da Working tree, commit e tagging Git, sincronização com o origin, publicação automática da GitHub Release extraindo o corpo do `CHANGELOG.md` (Single Source of Truth) e upload do ZIP |

> [!NOTE]
> **Ferramenta Técnica de Manutenção:** O script `build_release.ps1` permanece exclusivamente como uma ferramenta técnica complementar do pipeline, destinada à reconstrução manual ou testes isolados do pacote ZIP. Ele **não** compõe o fluxo de execução ativo do operador.

> [!IMPORTANT]
> O Pipeline Oficial de Release do projeto é composto por apenas **duas etapas ativas obrigatórias** (Prepare e Publish). Suas saídas de console e blocos de status utilizam estritamente codificação de texto ASCII sem dependência de caracteres Unicode (usando marcadores `[OK]`, `[INFO]`, `[WARN]` e `[ERRO]`). O final do script `publish_release.ps1` gera obrigatoriamente o painel "RESUMO FINAL DA RELEASE" contendo 10 chaves de status alinhadas de forma uniforme.

### Fluxo Operacional de Execução
O fluxo de publicação de novas versões segue obrigatoriamente a sequência de duas etapas:

1.  **Etapa 1: Prepare Release (Preparação e Build):** O operador dispara ativamente o script informando a versão desejada:
    ```powershell
    powershell -ExecutionPolicy Bypass -File scripts/prepare_release.ps1 -Version X.Y.Z
    ```
    O script valida o padrão de versão, atualiza os arquivos estruturais do plugin, lê dinamicamente as seções `## Resumo para Release` de todos os relatórios da versão corrente no diretório `docs/releases/`, consolida as informações por categoria no `CHANGELOG.md` (Single Source of Truth) e aciona automaticamente a geração e a auditoria estrutural (.NET) do ZIP.
2.  **Etapa 2: Publish Release (Publicação e Sincronização):** O operador executa o script de publicação para efetivar a release:
    ```powershell
    powershell -ExecutionPolicy Bypass -File scripts/publish_release.ps1
    ```
    O script audita a Working Tree, cria o commit e a tag correspondentes, sincroniza as alterações com a branch remota principal, localiza e extrai de forma automática o bloco da versão corrente no `CHANGELOG.md` e o utiliza integralmente como as Release Notes da release criada no GitHub via GitHub CLI (`gh`), fazendo o upload do pacote ZIP e concluindo o processo de forma unificada. Adiciona dinamicamente com `git add` todos os documentos de release à Working Tree limpa, bloqueando o deploy em caso de qualquer alteração de código externa inesperada.

---

## 📁 Repository Bootstrap e Governança

Cada versão de release deve garantir a integridade dos metadados de governança na pasta de desenvolvimento do plugin:

*   **CHANGELOG.md:** Histórico estruturado sob o padrão "Keep a Changelog".
*   **LICENSE:** Termo de licença proprietária comercial restrita para proteção da propriedade intelectual da TD Vieira Design.
*   **CONTRIBUTING.md:** Manual de convenções Git Flow e Commits Semânticos para novos desenvolvedores.
*   **SECURITY.md:** Diretrizes de reporte confidencial de vulnerabilidades encontradas no ecossistema.
*   **Controle de Commits (Normalização):**
    *   `.gitignore`: Filtra resíduos do LocalWP, dumps SQL de backup (`backup.sql`), dumps ZIP temporários e pastas de cache de IDEs.
    *   `.gitattributes`: Normaliza quebras de linha (`* text eol=lf`) para compatibilidade perfeita entre ambientes de build Linux, macOS e Windows.

---

## 🧹 Distribution Validation (Validação de Distribuição)

O pacote de distribuição de produção enviado para instalação do cliente final deve conter **apenas** arquivos funcionais homologados. 

### 1. Arquivos Excluídos do ZIP de Produção (Exclusivos do Repositório Git)
Os seguintes arquivos são importantes para o controle de desenvolvimento no GitHub, mas **não devem** ser inseridos no zip final enviado ao cliente:
*   `.git` e subpastas.
*   `.gitignore` e `.gitattributes`.
*   `CONTRIBUTING.md` e `SECURITY.md`.
*   Quaisquer scripts utilitários Python (`.py`) ou arquivos de lote (`.bat`, `.sh`).
*   A pasta técnica `/docs` contendo este handbook e seus relatórios.

### 2. Purga de Resíduos Locais
Antes de fechar o zip, o Release Builder deve verificar se arquivos de depuração e QA gerados localmente estão isolados fora da pasta física do plugin (preferencialmente na raiz pública `/public/` do LocalWP). Esses arquivos incluem:
*   `functional_test_plan.md` e `functional_test_report.md`
*   `release_readiness_report.md` ou `release_certification_report.md`
*   `autologin.php` (script de bypass de login local)

### 3. Validação Prática de Instalação e Ativação no WordPress
Como critério obrigatório e intransponível de homologação, o Release Builder deve testar fisicamente o pacote ZIP final gerado em `build/gerador-posts-gemini.zip` em um ambiente WordPress ativo (local ou sandbox), executando as seguintes ações de validação:
*   **Instalação Limpa:** Fazer o upload manual do ZIP na seção de Plugins e verificar se a instalação é concluída com sucesso.
*   **Atualização de Versão:** Instalar a versão anterior e aplicar a atualização por cima usando o novo ZIP, certificando-se de que a estrutura e opções antigas persistam no banco de dados.
*   **Ativação e Execução:** Ativar o plugin e navegar no painel administrativo. O teste deve provar que não há o erro de ativação "O arquivo do plugin não existe" e nenhuma regressão sintática ou funcional.
Nenhuma Release será homologada para publicação oficial sem preencher este requisito prático de aprovação.

---

## 🏷️ Procedimento de Git e Tagging Semântico

Com a pasta do plugin validada e limpa, executa-se a sequência de comandos Git:

```powershell
# 1. Adicionar os arquivos funcionais e de governança na branch principal
git add wp-content/plugins/gerador-posts-gemini/

# 2. Criar o commit semântico de release
git commit -m "release(v1.0.0): primeira versão oficial do Gerador de Posts (IA)"

# 3. Gerar a Tag anotada e assinada correspondente à release
git tag -a v1.0.0 -m "Release oficial v1.0.0"

# 4. Enviar os commits e a Tag para o repositório remoto oficial
git push origin master --tags
```

---

## ⚙️ Instalação e Validação do GitHub CLI (gh)

Para automatizar totalmente a publicação de releases e o upload do pacote ZIP no GitHub a partir do console local de desenvolvimento, o utilitário GitHub CLI deve estar configurado no sistema.

*   **Windows (Método Oficial Principal):**
    ```powershell
    winget install --id GitHub.cli
    ```
*   **Instalação Manual (Alternativa):** Download do instalador executável diretamente em: https://cli.github.com/
*   **Autenticação:** Após instalar, execute `gh auth login` no terminal e autentique sua conta.

**Validação e Execução de Comandos Externos:** O script de publicação centraliza todas as execuções de comandos externos do Git e do GitHub CLI (`gh`) em uma única função auxiliar reutilizável, baseando-se exclusivamente nos códigos de retorno de execução (`$LASTEXITCODE`). Isso inclui `git status`, `git add`, `git commit`, `git push`, `git tag`, `gh auth status`, `gh repo view`, `gh release view`, `gh release create` e `gh release upload`. Ao remover o uso de redirecionamentos complexos como `2>&1` e descartar qualquer parsing textual da saída para fins de tomada de decisões, o pipeline garante imunidade absoluta contra variações linguísticas do sistema, traduções de console, versões das ferramentas ou modificações no formato da saída de texto.

---

## 📦 Empacotamento e Upload Manual (GitHub)

1.  **Geração do ZIP de Produção:**
    *   O ZIP deve ser gerado de forma totalmente automatizada rodando o script de build contido no repositório. Abra o console do PowerShell na raiz do projeto e execute:
        ```powershell
        powershell -ExecutionPolicy Bypass -File scripts/build_release.ps1
        ```
    *   O ZIP será criado de forma limpa sob a pasta `build/` contendo unicamente a pasta do plugin `/gerador-posts-gemini/` e seus subdiretórios de produção. É terminantemente proibido manter ou gerar arquivos ZIP na raiz do repositório.
    *   Caminho de saída: `build/gerador-posts-gemini.zip`.
2.  **Upload e Publicação Remota:**
    *   Como tokens locais de CLI (`gh`) podem expirar ou não estar configurados no terminal de desenvolvimento local, a publicação final deve ser complementada manualmente no painel do GitHub.
    *   Acesse: `https://github.com/tdvie/gerador_posts/releases/new`.
    *   Selecione a tag **`v2.0.3`** criada via Git.
    *   Configure o título como `v2.0.3` e copie as notas de alteração do `CHANGELOG.md` na descrição.
    *   Arraste e anexe o arquivo compactado `build/gerador-posts-gemini.zip` gerado.
    *   Clique em **Publish release**.

---

## 🚦 Matriz de Decisão de Release (GO / NO-GO)

O encerramento da release e liberação de pacotes comerciais seguem a classificação abaixo:

### 🟢 GO (Aprovado sem restrições)
*   **Critérios:** 100% dos testes funcionais com sucesso. Ausência total de erros sintáticos, vulnerabilidades ativas (SSRF mitigado, Nonce e Capabilities ativos). Processamento de imagem WebP e crop funcionando perfeitamente em homologação.
*   **Ação:** Disparar tagging, push e upload do ZIP.

### 🟡 GO COM RESSALVAS (Aprovado com ações corretivas complementares)
*   **Critérios:** O software atende 100% aos requisitos de qualidade lógica e de segurança do WordPress, mas etapas de infraestrutura externa de rede de desenvolvimento impedem a automatização total (ex: ausência de token de API GitHub CLI para upload do ZIP no terminal, dependendo de upload manual via navegador).
*   **Ação:** Finalizar manualmente o processo e registrar a ressalva de ambiente nos relatórios de execução da release.

### 🔴 NO-GO (Publicação abortada)
*   **Critérios:** Qualquer uma das seguintes condições suspende a release:
    *   Falhas de Nonce ou permissão administrativa em endpoints AJAX.
    *   Desativação permanente da validação SSL (`sslverify = false`) fora de ambientes locais.
    *   Vulnerabilidade ativa de SSRF no download de imagens.
    *   Falha em testes funcionais primários (ex: erro 500 no salvamento do post).
    *   Erros de lint críticos (ex: `SyntaxError` ou `FatalError`) in PHP/JS.
*   **Ação:** Cancelar o ciclo de build, reverter tags criadas localmente e retornar a tarefa para a fase de implementação.
