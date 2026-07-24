# Sincronização e Atualização de Memória (Memory Update Workflow)

Este workflow operacional orienta a rotina de atualização e sincronização dos arquivos de memória persistente da arquitetura assistida por IA ao final de turnos ou tarefas de engenharia.

---

## 💾 Roteiro de Atualização de Contexto

### 1. Atualização do Snapshot de Status
*   Ao concluir qualquer tarefa ou evolução, editar o arquivo [project-status.md](../memory/project-status.md).
*   Garantir a atualização do campo de versão estável (`Version: X.Y.Z`) e o preenchimento da tabela de metadados.
*   Registrar todas as auditorias e revisões de qualidade que foram concluídas na sessão.

### 2. Registro de Novas Decisões Técnicas (ADR)
*   Caso a iteração tenha envolvido novas refatorações, adoções de bibliotecas ou alterações na persistência de dados, registrar a decisão cronológica no arquivo [tech-decisions.md](../memory/tech-decisions.md) como ADR.
*   Cada ADR deve respeitar a estrutura sintática de Contexto, Decisão e Impacto.

### 3. Saneamento do Índice
*   Verificar se novos manuais ou arquivos de memória foram criados. Se sim, garantir que estejam devidamente catalogados no roteador [MEMORY.md](../memory/MEMORY.md).
