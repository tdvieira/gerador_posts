# Regras do Domínio Git e Versionamento (Regras do Domínio Git)

Este documento define as diretrizes normativas para versionamento, nomenclatura de branches, mensagens de commit e segurança do histórico de desenvolvimento do repositório.

---

## 🚦 1. Estrutura de Ramificação (Branching Strategy)

*   **Rastreabilidade por Issues:** O ciclo de desenvolvimento deve ser estruturado em torno de Issues do GitHub, abandonando-se o modelo de marcos rígidos no core do repositório.
*   **Prefixos Oficiais de Branches:**
    *   `feature/nome-da-feature` - Novas funcionalidades ou evoluções da arquitetura.
    *   `fix/nome-do-bug` - Correções de defeitos operacionais ou segurança.
    *   `hotfix/correcao-critica` - Ajustes rápidos de emergência na branch principal.

---

## 📝 2. Mensagens de Commit (Conventional Commits)

Todas as mensagens de commit devem seguir o padrão sintático dos *Conventional Commits*:

`tipo(escopo): descrição concisa em letras minúsculas`

### Tipos Permitidos:
*   `feat`: Adição de nova funcionalidade ou componente arquitetural.
*   `fix`: Resolução de bugs operacionais ou falhas de sistema.
*   `docs`: Edição exclusiva de documentação técnica, manuais em `/docs` ou relatórios.
*   `refactor`: Alteração de código-fonte que não corrige bug nem adiciona feature (simplificação sob SRP).
*   `chore`: Manutenção de dependências, builds ou configurações de arquivos de ignorados.
*   `release`: Commits dedicados a bumps de versão SemVer e publicação.

---

## 🔒 3. Segurança e Segredos

*   **Exclusão de Credenciais:** É terminantemente proibido versionar credenciais locais, chaves de API de provedores de IA (Gemini, OpenAI, Groq), senhas de banco de dados ou nonces estáticos.
*   **Arquivo de Ignorados (`.gitignore`):** Deve ser ativamente auditado para evitar a inclusão acidental de dumps SQL (`*.sql`), arquivos zipados de staging, caches do WordPress e pastas locais temporárias (`/tmp/`, `/scratch/`).
