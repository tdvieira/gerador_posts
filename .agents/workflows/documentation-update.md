# Atualização de Manuais Técnicos (Documentation Update Workflow)

Este workflow operacional orienta o processo de edição, saneamento e inclusão de manuais no Developer Handbook localizado na pasta oficial `/docs/` do repositório.

---

## 📄 Roteiro de Atualização de Documentação

### 1. Escopo de Intervenção
*   Ler as normas permanentes de portabilidade em [documentation.md](../rules/documentation.md).
*   Mapear se a alteração requer a atualização de um manual existente ou a criação de uma nova especificação técnica (SRP documental).

### 2. Formatação e Convenção de Links
*   Assegurar que toda referência externa para a raiz pública do WordPress (`public/`) suba exatamente 4 níveis a partir de `/docs/` (ex: `../../../../wp-config.php`).
*   Verificar que toda referência cruzada para a governança interna `.agents/` utilize 1 nível de subida (ex: `../.agents/rules/project-governance.md`).
*   Garantir a presença obrigatória das seções **"Quando consultar este documento?"** no topo e **"Documentos relacionados"** no rodapé de cada manual técnico.
