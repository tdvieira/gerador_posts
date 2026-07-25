# Desenvolvimento de Funcionalidades do Plugin (Fluxo de Trabalho de Desenvolvimento de Funcionalidades)

Este fluxo de trabalho operacional orienta o ciclo prático de engenharia para criar e evoluir funcionalidades do plugin **Gerador de Posts (IA)** em conformidade com as diretrizes do WordPress.

---

## 🚦 1. Carregamento de Integração Obrigatório
*   O agente de IA inicia a tarefa consultando obrigatoriamente o inicializador técnico [AGENT.md](../../AGENT.md) na raiz do plugin.
*   Ler as regras de domínio sob `.agents/rules/` (especialmente `git.md`, `documentation.md` e o manual de engenharia [engineering.md](../rules/engineering.md)).
*   **Carregamento de Memória sob Demanda:** O agente de IA pode ler opcionalmente o manual de regras de negócio em [blog-architecture.md](../memory/blog-architecture.md) se necessitar de contexto técnico sobre o funcionamento do WordPress local ou premissas do plugin.

---

## 💻 2. Conformidade com Padrões Técnicos Permanentes
*   Todo o desenvolvimento de código-fonte, scripts, estilos e proteções de endpoints do WordPress deve respeitar estritamente as regras de arquitetura e segurança definidas em [engineering.md](../rules/engineering.md).
*   Antes de submeter código para validação, o agente deve garantir que a separação de conceitos, a proteção de endpoints AJAX com nonces e capabilities, e a compatibilidade multiplataforma de caminhos estejam em conformidade com as regras em [engineering.md](../rules/engineering.md).

---

## 🚦 3. Validação Local
*   Finalizado o desenvolvimento, o desenvolvedor ou o agente deve acionar os checklists locais descritos no fluxo de trabalho de validação de qualidade ([qa-validation.md](qa-validation.md)).
