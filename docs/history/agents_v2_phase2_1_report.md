# Relatório de Migração — Arquitetura .agents v2 (Fase 2.1: Limpeza Arquitetural)

Este relatório registra a inclusão incremental de governança e a validação de integridade do hotfix da **Fase 2.1** para a nova estrutura `.agents` v2.

---

## 📝 Alteração Realizada

Foi atualizado exclusivamente o arquivo de princípios de governança do projeto:
*   **[project-governance.md](./.agents/rules/project-governance.md) (Linha 24):** Adição do Princípio 12, denominado **Princípio da Limpeza Arquitetural (Architectural Cleanliness Principle)**.

---

## 🏛️ Justificativa Arquitetural

A inclusão deste princípio estabelece uma norma permanente para a saúde de longo prazo do repositório:
1.  **Saneamento Sistemático:** Previnedores contra commits acidentais de arquivos temporários de teste, dumps de banco expirados, artefatos de depuração e rascunhos de desenvolvimento.
2.  **Organização Ativa:** Força a remoção automática de arquivos marcadores `.gitkeep` tão logo um diretório do ecossistema `.agents` passe a contar com arquivos de governança permanentes.
3.  **Facilidade de Auditoria:** Garante que Pull Requests e releases comerciais contenham estritamente arquivos válidos e permanentes, agilizando revisões por pares e execuções de testes automatizados.
4.  **Isolamento Operacional:** Mapeia a obrigatoriedade de justificar nos relatórios de fase correspondentes quaisquer arquivos temporários que necessitem permanecer ativos temporariamente por dependência de etapas posteriores.

---

## 🚦 Validação de Integridade e Mínima Intervenção

*   **Arquivo Modificado Único:** Apenas o arquivo [project-governance.md](./.agents/rules/project-governance.md) foi alterado.
*   **Sem Alterações Sintáticas Externa:** Nenhum código-fonte (PHP, JS, CSS) ou configuração do plugin WordPress foi tocado.
*   **Isolamento Documental:** Nenhum manual técnico ou relatório em `/docs` foi editado.
*   **Preservação de Outras Regras:** As regras normativas criadas na Fase 2 nos arquivos `git.md`, `documentation.md`, `memory.md`, `workflows.md` e `prompts.md` permaneceram intocadas.
*   **Conformidade de Marcadores:** Os arquivos `.gitkeep` nos diretórios ainda vazios (`memory/`, `workflows/`, `prompts/`, `reports/`) foram preservados e mantidos, conforme diretrizes aprovadas.
