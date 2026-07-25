# Validação de Qualidade e Garantia de Qualidade (Fluxo de Trabalho de Validação de Qualidade)

Este fluxo de trabalho operacional descreve as rotinas e checagens práticas de qualidade de software para homologação de novas funcionalidades ou correções no plugin **Gerador de Posts (IA)**.

---

## 🚦 1. Carregamento de Integração Obrigatório
*   O agente de IA inicia a tarefa consultando obrigatoriamente o inicializador técnico [AGENT.md](../../AGENT.md) na raiz do plugin.
*   Consultar as regras técnicas permanentes em [engineering.md](../rules/engineering.md).
*   **Carregamento de Memória sob Demanda:** O agente deve carregar o snapshot de status em [project-status.md](../memory/project-status.md) sob demanda ao término das checagens para registrar os resultados de cobertura de testes.

---

## 🚦 2. Roteiro de Testes e Validação de Garantia de Qualidade

### Passo 1: Testes de Segurança (Nonces e Capabilities)
*   **Verificação de AJAX:** Forçar chamadas de AJAX administrativas sem nonces válidos para atestar que o backend rejeita com erro HTTP 403.
*   **Verificação de Escalonamento:** Tentar invocar os endpoints com perfil de usuário sem permissões (`subscriber`, `contributor`), validando que o CMS bloqueia a ação por insuficiência de permissões (`manage_options`).

### Passo 2: Testes Funcionais e Integrações
*   Validar o processamento e download de mídias de IAs sob o comportamento do User-Agent modificado para evitar bloqueios.
*   Testar a persistência correta de transients de cache no banco de dados local após novas gerações de posts.

### Passo 3: Scripts de Validação Locais
*   Quando disponíveis na infraestrutura local do AG Kit (pasta externa de suporte do desenvolvedor), executar os scripts utilitários automatizados de conformidade (ex: `checklist.py`).
*   Registrar todas as evidências físicas de garantia de qualidade (QA) e cobertura de testes para compor a tabela de status do projeto.
