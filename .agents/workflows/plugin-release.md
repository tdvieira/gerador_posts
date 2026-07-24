# Processo de Release do Plugin (Plugin Release Workflow)

Este workflow operacional orienta o empacotamento prático e a distribuição de novas versões estáveis de mercado (dist) do plugin **Gerador de Posts (IA)**.

---

## 📦 Roteiro de Empacotamento Comercial (ZIP)

### 1. Pré-Requisitos e Versão
*   Garantir a conformidade estrita da branch principal.
*   Atualizar o cabeçalho do plugin `gerador-posts-gemini.php` (`Version: X.Y.Z`) e o snapshot de status [project-status.md](../memory/project-status.md).

### 2. Higienização para ZIP de Produção
O arquivo de distribuição compactada (.zip) deve conter exclusivamente os arquivos necessários de execução do CMS.
*   **Proibido incluir:** A pasta `.git/`, arquivos `.gitignore`, `.gitattributes`, logs de testes locais, arquivos do scratch de desenvolvimento e backups SQL.
*   **Decisão de Staging de IA:** A pasta `.agents/` é mantida em produção por acoplar a governança e memória necessárias para sessões de manutenção futura assistida por IA.

### 3. Geração do Pacote e Versionamento
*   Empacotar o plugin em arquivo zip com a nomenclatura estruturada do plugin (`gerador-posts-gemini.zip`).
*   Criar o commit de release sob a tag `vX.Y.Z` em conformidade com as regras de Git (`git.md`).
*   Subir a branch e a tag correspondente para o GitHub.
