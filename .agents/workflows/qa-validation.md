# Validação de Qualidade e QA (QA Validation Workflow)

Este workflow operacional descreve as rotinas e checagens práticas de qualidade de software para homologação de novas features ou correções no plugin **Gerador de Posts (IA)**.

---

## 🚦 Roteiro de Testes e Validação de QA

### 1. Testes de Segurança (Nonces e Capabilities)
*   **Verificação de AJAX:** Forçar chamadas de AJAX administrativas sem nonces válidos para atestar que o backend rejeita com erro HTTP 403.
*   **Verificação de Escalonamento:** Tentar invocar os endpoints com perfil de usuário sem permissões (`subscriber`, `contributor`), validando que o CMS bloqueia a ação por insuficiência de permissões (`manage_options`).

### 2. Testes Funcionais e Integrações
*   Validar o processamento e download de mídias de IAs sob o comportamento do User-Agent modificado para evitar bloqueios.
*   Testar a persistência correta de transients de cache no banco de dados local após novas gerações de posts.

### 3. Scripts de Validação Locais
*   Quando disponíveis na infraestrutura local do AG Kit (pasta externa de suporte do desenvolvedor), executar os scripts utilitários automatizados de conformidade (ex: `checklist.py`).
*   Registrar todas as evidências físicas de QA e cobertura de testes para compor a tabela de status do projeto.
