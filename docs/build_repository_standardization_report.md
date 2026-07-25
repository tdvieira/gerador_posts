# Relatório de Padronização do Repositório — v1.0.0

Este relatório documenta as ações operacionais para a padronização final do repositório do plugin **Gerador de Posts (IA)**, estabelecendo a pasta `build/` como repositório exclusivo de artefatos compactados e saneando a organização da raiz.

---

## 📁 1. Arquivos Modificados e Criados

Os seguintes ajustes de conformidade física e taxonômica foram aplicados:

1.  **`build/` (Criado):** Nova subpasta oficial na raiz do repositório, configurada para receber exclusivamente os artefatos binários consolidados de distribuição.
2.  **[build/gerador-posts-gemini.zip](build/gerador-posts-gemini.zip) (Movido):** O ZIP original de distribuição foi realocado da raiz para a nova pasta dedicada, proibindo pacotes binários na raiz.
3.  **[README.md](README.md) (Modificado):** Adicionada a seção "Estrutura do Projeto" esclarecendo a finalidade das pastas `docs/`, `build/` e `.agents/`, diferenciando o escopo operacional do plugin e os arquivos internos do framework de desenvolvimento Antigravity.
4.  **[docs/releases/RELEASE_PROCESS.md](docs/releases/RELEASE_PROCESS.md) (Modificado):** Ajustadas as instruções operacionais de build e upload manual do painel do GitHub para referenciar a geração obrigatória de pacotes em `build/gerador-posts-gemini.zip`.
5.  **[.agents/rules/documentation.md](.agents/rules/documentation.md) (Modificado):** Adicionado o item 6 à seção "Organização e Localização" de diretrizes normativas de governança, consolidando que artefatos de build pertencem à pasta `build/` e proibindo arquivos ZIP na raiz.

---

## 📜 2. Regras de Governança Oficializadas

A nova regra incorporada à governança e documentação é:
*   **Regra de Distribuição em build/:** Todo arquivo compactado (ZIP) gerado para instalação ou atualização do plugin deve residir e ser gerado exclusivamente sob o diretório `build/`. É terminantemente proibido manter pacotes de build na raiz do projeto, mantendo o ponto de entrada principal do plugin isolado de dados binários pesados.

---

## 📝 3. Validações Executadas

*   **Execução do Pipeline de Build:** O script PowerShell `build_release.ps1` foi atualizado para automatizar a geração do ZIP na pasta `build/` e executado em console com sucesso.
*   **Varredura de Links Relativos:** Confirmada a consistência de todos os links relativos entre documentos markdown pós-build.
*   **Integridade do Escopo:** Nenhuma alteração foi realizada em arquivos PHP, JS ou CSS de negócios, salvaguardando a estabilidade da aplicação.

A estrutura de distribuição do repositório está **aprovada** e padronizada.

---

## 🚦 Bloco de Status do Relatório
*   **Status:** Concluído
*   **Fase:** Padronização Final da Distribuição e build/
*   **Resultado:** Aprovado (Diretório build/ Configurado, ZIP de Produção Realocado e README.md Atualizado)
*   **Validação:** Execução do Pipeline de Build, Validação de Versionamento e Auditoria de Organização da Raiz
