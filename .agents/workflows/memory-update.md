# Sincronização e Atualização de Memória (Fluxo de Trabalho de Atualização de Memória)

Este fluxo de trabalho operacional orienta a rotina de atualização e sincronização dos arquivos de memória persistente da arquitetura assistida por IA ao final de turnos ou tarefas de engenharia.

---

## 🚦 1. Carregamento de Integração Obrigatório
*   O agente de IA inicia a tarefa consultando obrigatoriamente o inicializador técnico [AGENT.md](../../AGENT.md) na raiz do plugin.
*   Consultar as regras de memória em [memory.md](../rules/memory.md).
*   **Carregamento de Memória sob Demanda:** Carregar o snapshot de status em [project-status.md](../memory/project-status.md) e o índice em [MEMORY.md](../memory/MEMORY.md) para realizar as devidas atualizações na base de conhecimento do projeto.

---

## 💾 2. Roteiro de Atualização de Contexto

### Passo 1: Atualização do Snapshot de Status
*   Ao concluir qualquer tarefa ou evolução, editar o arquivo [project-status.md](../memory/project-status.md).
*   Garantir a atualização do campo de versão estável (`Version: X.Y.Z`) e o preenchimento da tabela de metadados.
*   Registrar todas as auditorias e revisões de qualidade que foram concluídas na sessão.

### Passo 2: Registro de Novas Decisões Técnicas (ADR)
*   Caso a iteração tenha envolvido novas refatorações, adoções de bibliotecas ou alterações na persistência de dados, registrar a decisão cronológica no arquivo [tech-decisions.md](../memory/tech-decisions.md) como Registro de Decisão Arquitetural (ADR).
*   Cada registro deve respeitar a estrutura sintática de Contexto, Decisão e Impacto.

### Passo 3: Saneamento do Índice
*   Verificar se novos manuais ou arquivos de memória foram criados. Se sim, garantir que estejam devidamente catalogados no roteador [MEMORY.md](../memory/MEMORY.md).
