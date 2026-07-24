# Índice da Arquitetura de Agentes (Architecture Index)

Este documento atua como o mapa de navegação e taxonomia da infraestrutura `.agents` do projeto. Ele descreve a finalidade de cada subdiretório e orienta sobre onde cada tipo de conhecimento deve ser catalogado.

---

## 🗺️ Mapa de Diretórios e Conhecimento

### 1. `rules/`
*   **Finalidade:** Hospedar as normas permanentes de governança do projeto, regras sintáticas e políticas específicas de cada domínio.
*   **Localização de Regras:** 
    *   Princípios gerais e hierarquia de autoridade → [project-governance.md](./rules/project-governance.md)
    *   Regras do domínio Git e Versionamento → [git.md](./rules/git.md)
    *   Regras de Documentação Técnica → [documentation.md](./rules/documentation.md)
    *   Regras de Escrita e Leitura de Memória → [memory.md](./rules/memory.md)
    *   Regras de Automações e Scripts → [workflows.md](./rules/workflows.md)
    *   Regras de Prompts reutilizáveis → [prompts.md](./rules/prompts.md)

### 2. `memory/`
*   **Finalidade:** Armazenar os metadados de status dinâmicos do projeto, o histórico cronológico de decisões técnicas e o snapshot de pendências.
*   **Tipo de Conhecimento:** Arquivos permanentes de contexto de sessão dos agentes (como `project-status.md` e `tech-decisions.md`).

### 3. `workflows/`
*   **Finalidade:** Centralizar as rotinas operacionais estruturadas de auditorias e checklists de testes funcionais.
*   **Tipo de Conhecimento:** Arquivos operacionais e guias passo a passo de QA.

### 4. `prompts/`
*   **Finalidade:** Guardar prompts estruturados e modelos de contexto reutilizáveis.
*   **Tipo de Conhecimento:** Prompts sistêmicos com parâmetros lógicos abstratos de execução.

### 5. `reports/`
*   **Finalidade:** Agrupar os relatórios de execuções de testes, vulnerabilidades e qualidade de código.
*   **Tipo de Conhecimento:** Artefatos de saída das ferramentas de validação de QA.
