# Regras do Domínio de Fluxos de Trabalho (Regras do Domínio de Fluxos de Trabalho)

Este documento estabelece as diretrizes normativas para estruturação, execução e persistência de fluxos de trabalho operacionais de garantia de qualidade (QA) e integração de sistema.

---

## 🔄 1. Tipologia de Fluxos de Trabalho

*   **Fluxos de Trabalho Genéricos:** Processos lógicos reutilizáveis de ciclo de vida (como execução de fase, auditoria e geração de relatórios). Devem ser mantidos 100% abstratos e portáveis.
*   **Fluxos de Trabalho Específicos:** Processos operacionais práticos dedicados a tarefas do plugin (desenvolvimento de funcionalidades, preparação de pacotes ZIP, controle de AJAX e Nonces). Podem referenciar tecnologias específicas.

---

## 🚦 2. Diretrizes de Execução e Persistência

*   **Links para Documentos de Autoridade:** Os arquivos de fluxos de trabalho não definem novas regras normativas. Devem herdar a governança apontando diretamente via links markdown relativos para os arquivos de domínio em `.agents/rules/`.
*   **Checklist de Validação Obrigatório:** Todo fluxo de trabalho que guie o encerramento de tarefas deve incluir um checklist físico de conformidade de garantia de qualidade (QA).
*   **Logs Locais Temporários:** Qualquer registro de execução (log) gerado dinamicamente durante execuções locais de testes em segundo plano (como `checklist.py`) deve ser mantido restrito a diretórios ignorados ou excluído ativamente, proibindo-se commits de logs de execução no Git.
