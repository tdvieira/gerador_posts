# Pipeline Oficial de Release — Manual de Operação

Este manual descreve a arquitetura, o fluxo de execução e a interface de console do **Pipeline Oficial de Release** do plugin **Gerador de Posts (IA)**.

---

## 🛠️ 1. Arquitetura do Pipeline

O Pipeline Oficial de Release é composto por três scripts especializados sob o princípio da responsabilidade única (SRP), garantindo reprodutibilidade completa e segurança absoluta:

1.  **`prepare_release.ps1` (Preparação):**
    *   Valida a sintaxe da versão (`MAJOR.MINOR.PATCH`).
    *   Substitui de forma automática a versão corrente no cabeçalho do plugin, em constantes e em arquivos operacionais.
    *   Insere a nova seção correspondente no `CHANGELOG.md`.
    *   Varre referências antigas para impedir metadados inconsistentes.
    *   Dispara o build-script automaticamente.
2.  **`build_release.ps1` (Build e Validação Estrutural):**
    *   Limpa temporários e copia os arquivos produtivos do plugin para um espelho isolado.
    *   Remove arquivos de desenvolvimento (.gitkeep, etc.).
    *   Compacta o diretório de forma manual garantindo separadores `/` nos metadados do ZIP.
    *   Executa uma validação estrutural rígida de 8 critérios do WordPress. Se falhar, remove o ZIP.
3.  **`publish_release.ps1` (Publicação, Limpeza e Hardening):**
    *   Audita a árvore de trabalho (`git status --porcelain`).
    *   Comita as alterações administrativas permitidas de release.
    *   Gera a tag semântica local (`vMAJOR.MINOR.PATCH`).
    *   Sincroniza commits e tags com a branch remota `main`.
    *   Publica a release no GitHub anexando o ZIP através do GitHub CLI (`gh`).
    *   **Limpeza e Git Add Dinâmico:** Remove resíduos da pasta `temp_zip/`. Identifica dinamicamente por padrões (wildcards) todos os arquivos Markdown oficiais em `docs/releases/*.md` (relatórios técnicos, manuais operacionais) e os adiciona automaticamente com `git add` no commit de publicação, sem necessidade de atualizar o script. Caso reste qualquer alteração em arquivos externos ao processo de release ou código de desenvolvimento alheio, a publicação é cancelada por segurança.
    *   Executa a validação final da consistência de produção.

---

## ⚙️ 2. Instalação e Validação do GitHub CLI (gh)

Para automatizar totalmente a publicação de releases e o upload do pacote ZIP no GitHub, o utilitário GitHub CLI deve estar configurado no sistema.

*   **Windows (Método Oficial Principal):**
    ```powershell
    winget install --id GitHub.cli
    ```
*   **Instalação Manual (Alternativa):** Baixe o instalador executável diretamente em [cli.github.com](https://cli.github.com/).
*   **Autenticação:** Após instalar, execute `gh auth login` no terminal e autentique sua conta.

### Validação e Execução de Comandos Externos
Para certificar a resiliência técnica em ambientes de integração heterogêneos, todas as validações e execuções de comandos externos do Git e do GitHub CLI (`gh`) no pipeline utilizam estritamente os códigos de retorno de execução do terminal (`$LASTEXITCODE`), por meio de uma função auxiliar reutilizável (`Execute-ExternalCommand`) declarada no próprio script.

Isso engloba checagens e operações como `git status`, `git add`, `git commit`, `git push`, `git tag`, `gh auth status`, `gh repo view`, `gh release view`, `gh release create` e `gh release upload`. Essa arquitetura elimina qualquer dependência de redirecionamentos de saída complexos (como `2>&1`) ou de parsing textual da saída dos comandos, tornando o pipeline 100% independente de idioma, tradução do console, versão ou formatação textual das ferramentas Git e GitHub CLI.

---

## 🚦 3. Interface de Console (ASCII Padrão)

Para assegurar compatibilidade universal com qualquer plataforma e sistema operacional (PowerShell, CMD, bash, GitHub Actions, Linux/macOS), toda a interface de console foi construída em codificação de texto ASCII sem dependência de caracteres Unicode complexos ou acentuações.

As mensagens do terminal seguem obrigatoriamente a seguinte taxonomia:
*   `[OK]`: Sucesso em testes, validações e ações do Git.
*   `[INFO]`: Mensagens explicativas e de progresso operacional.
*   `[WARN]`: Avisos secundários de atenção.
*   `[ERRO]`: Erros de integridade que abortam imediatamente a execução.

---

## 📊 4. Painéis de Fechamento do Pipeline

Ao concluir a publicação com sucesso, o console exibe dois painéis estruturados de resumo e validação:

### RESUMO FINAL DA RELEASE
```
==================================================
RESUMO FINAL DA RELEASE
==================================================
Versao Publicada   : v2.0.1
Branch             : main
Commit             : 2862210...
Tag Git            : v2.0.1
Caminho ZIP Gerado : build/gerador-posts-gemini.zip
Status Validacao   : APROVADA [OK]
Status do Push     : CONCLUIDO [OK]
Status da GH Rel   : v2.0.1 [APROVADA]
URL da Release     : https://github.com/tdvieira/gerador_posts/releases/tag/v2.0.1
Data e Hora Pub    : 2026-07-27 10:15:00
Status Final       : PUBLICADO COM SUCESSO [OK]
==================================================
```

### PIPELINE OFICIAL DE RELEASE FINALIZADO
```
==================================================
PIPELINE OFICIAL DE RELEASE FINALIZADO
==================================================
Versao         : v2.0.1 [APROVADA]
ZIP            : build/gerador-posts-gemini.zip [APROVADO]
Git            : branch main [APROVADO]
Tag            : v2.0.1 [APROVADA]
Release GitHub : v2.0.1 [APROVADA]
Working Tree   : Limpa [OK]
==================================================
PUBLICACAO CONCLUIDA COM SUCESSO
==================================================
```
