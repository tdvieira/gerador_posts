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
    *   **Limpeza e Git Add Automático:** Remove resíduos da pasta `temp_zip/`. Adiciona automaticamente com `git add` todos os relatórios e manuais de documentação gerados pelo próprio pipeline. Caso reste qualquer alteração estranha (código pendente), a publicação é cancelada por segurança.
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

### Validação em Duas Etapas
Para certificar a resiliência técnica em ambientes de integração heterogêneos, a verificação do utilitário `gh` ocorre em duas fases consecutivas:
1.  **Fase 1: Autenticação (`gh auth status`):** Checa se as credenciais locais são válidas.
2.  **Fase 2: Acesso ao Repositório (`gh repo view`):** Testa se a leitura e gravação remota no repositório do projeto estão liberadas.

Ambas as checagens utilizam estritamente o código de retorno de execução do terminal (`$LASTEXITCODE`), garantindo total independência em relação ao idioma do console, tradução do texto de log ou formato de saída de novas versões do CLI.

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
