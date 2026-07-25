# Preparação de Publicação (Fluxo de Trabalho de Preparação de Publicação)

Este fluxo de trabalho define as etapas lógicas genéricas para planejar, validar e empacotar uma nova versão estável (Publicação) de produto no controle de versão Git.

---

## 🚦 1. Carregamento de Integração Obrigatório
*   O agente de IA inicia a tarefa consultando obrigatoriamente o inicializador técnico [AGENT.md](../../AGENT.md) na raiz do plugin.
*   Consultar as regras de controle de versão em [git.md](../rules/git.md).
*   **Carregamento de Memória sob Demanda:** O agente deve carregar o snapshot de status em [project-status.md](../memory/project-status.md) sob demanda para verificar a versão SemVer planejada antes de consolidar a publicação.

---

## 🔄 2. Roteiro de Preparação

### Passo 1: Validação Final de Integridade
*   Executar as ferramentas de garantia de qualidade (QA) e scripts locais de verificação física para garantir que a ramificação de homologação (branch de staging) está verde.

### Passo 2: Saneamento de Arquivos e Assets
*   Certificar que nenhum arquivo de cache de desenvolvimento, rascunhos de homologação ou credenciais privadas restaram no repositório.
*   Assegurar a ausência de arquivos marcadores `.gitkeep` em subpastas ativas.

### Passo 3: Determinação de Versão (SemVer)
*   Aplicar as regras de versionamento semântico (SemVer) no cabeçalho do arquivo principal e no `project-status.md` (ex: `v1.0.0` para major, `v1.0.1` para patch).

### Passo 4: Geração de Tag e Envio Remoto
*   Aplicar a tag correspondente da versão no commit consolidado.
*   Subir a ramificação estável (branch estável) e a tag correspondente para o repositório remoto.
