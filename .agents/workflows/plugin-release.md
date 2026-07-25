# Processo de Publicação do Plugin (Fluxo de Trabalho de Publicação do Plugin)

Este fluxo de trabalho operacional orienta o empacotamento prático e a distribuição de novas versões estáveis de mercado (distribuição) do plugin **Gerador de Posts (IA)**.

---

## 🚦 1. Carregamento de Integração Obrigatório
*   O agente de IA inicia a tarefa consultando obrigatoriamente o inicializador técnico [AGENT.md](../../AGENT.md) na raiz do plugin.
*   Consultar as regras de controle de versão em [git.md](../rules/git.md).
*   **Carregamento de Memória sob Demanda:** O agente deve carregar o snapshot de status em [project-status.md](../memory/project-status.md) sob demanda para atestar se a versão atual e os metadados estão corretos e prontos para empacotamento.

---

## 📦 2. Roteiro de Empacotamento Comercial (ZIP)

### Passo 1: Pré-Requisitos e Versão
*   Garantir a conformidade estrita da ramificação principal (branch principal).
*   Atualizar o cabeçalho do plugin `gerador-posts-gemini.php` (`Version: X.Y.Z`) e o snapshot de status [project-status.md](../memory/project-status.md).

### Passo 2: Higienização para ZIP de Produção
O arquivo de distribuição compactada (.zip) deve conter exclusivamente os arquivos necessários de execução do CMS.
*   **Proibido incluir:** A pasta `.git/`, arquivos `.gitignore`, `.gitattributes`, logs de testes locais, arquivos de rascunhos temporários de desenvolvimento e backups SQL.
*   **Decisão de Governança de IA:** A pasta `.agents/` é mantida em produção por acoplar a governança e memória necessárias para sessões de manutenção futura assistida por IA.

### Passo 3: Geração do Pacote e Versionamento
*   Empacotar o plugin em arquivo zip com a nomenclatura estruturada do plugin (`gerador-posts-gemini.zip`).
*   Criar o commit de publicação sob a tag `vX.Y.Z` em conformidade com as regras de Git (`git.md`).
*   Subir a ramificação (branch) e a tag correspondente para o GitHub.
