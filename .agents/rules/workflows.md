# Regras do Domínio de Workflows (Workflow Domain Rules)

Este documento estabelece as diretrizes normativas para estruturação, execução e persistência de workflows operacionais de QA e integração de sistema.

---

## 🔄 1. Tipologia de Workflows

*   **Workflows Genéricos:** Processos lógicos reutilizáveis de ciclo de vida (como execução de fase, auditoria e geração de relatórios). Devem ser mantidos 100% abstratos e portáveis.
*   **Workflows Específicos:** Processos operacionais práticos dedicados a tarefas do plugin (desenvolvimento de features, preparação de ZIPs, controle de AJAX/Nonces). Podem referenciar tecnologias específicas.

---

## 🚦 2. Diretrizes de Execução e Persistência

*   **Links para Documentos de Autoridade:** Os arquivos de workflows não definem novas regras normativas. Devem herdar a governança apontando diretamente via links markdown relativos para os arquivos de domínio em `.agents/rules/`.
*   **Checklist de Validação Obrigatório:** Todo workflow que guie o encerramento de tarefas deve incluir um checklist físico de conformidade de QA.
*   **Logs Locais Temporários:** Qualquer log gerado dinamicamente durante execuções locais de testes em segundo plano (como `checklist.py`) deve ser mantido restrito a diretórios ignorados ou excluído ativamente, proibindo-se commits de logs de execução no Git.
