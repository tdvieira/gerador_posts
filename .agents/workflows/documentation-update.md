# Atualização de Manuais Técnicos (Fluxo de Trabalho de Atualização de Documentação)

Este fluxo de trabalho operacional orienta o processo de edição, saneamento e inclusão de manuais no Manual do Desenvolvedor localizado na pasta oficial `docs/` do repositório.

---

## 🚦 1. Carregamento de Integração Obrigatório
*   O agente de IA inicia a tarefa consultando obrigatoriamente o inicializador técnico [AGENT.md](../../AGENT.md) na raiz do plugin.
*   Consultar as regras de documentação em [documentation.md](../rules/documentation.md).
*   **Carregamento de Memória sob Demanda:** Não há necessidade de carregar snapshots de status de memória para esta tarefa, exceto se houver atualização de metadados de documentação.

---

## 📄 2. Roteiro de Atualização de Documentação

### Passo 1: Escopo de Intervenção
*   Mapear se a alteração requer a atualização de um manual existente ou a criação de uma nova especificação técnica (Responsabilidade Única documental).

### Passo 2: Formatação e Convenção de Links
*   Assegurar que toda referência externa para a raiz pública do WordPress (`public/`) suba exatamente 4 níveis a partir de `docs/` (ex: `../../../../wp-config.php`).
*   Verificar que toda referência cruzada para a governança interna `.agents/` utilize 1 nível de subida (ex: `../.agents/rules/project-governance.md`).
*   Garantir a presença obrigatória das seções **"Quando consultar este documento?"** no topo e **"Documentos relacionados"** no rodapé de cada manual técnico.
