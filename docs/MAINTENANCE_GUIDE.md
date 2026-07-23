# Guia de Manutenção Evolutiva (Maintenance Guide) — v1.0.0

Este guia orienta engenheiros de software, administradores de sistemas e agentes inteligentes sobre os procedimentos e esteiras de execução obrigatórias a serem adotados para manter, corrigir e evoluir com segurança o plugin **Gerador de Posts (IA)**.

---

## ❓ Quando consultar este documento?

> [!IMPORTANT]
> Consulte este documento nas seguintes situações:
> *   Antes de iniciar qualquer alteração sintática no código PHP, estilo CSS ou script JS do plugin.
> *   Ao abrir uma nova ramificação de tarefas vinculada a uma Issue do GitHub para correção de bugs ou novas integrações.
> *   Durante a esteira de preparação e certificação de uma release de produção.
> *   Ao encerrar uma sessão de pair programming para registrar o andamento na memória do projeto.

---

## 📖 Índice

1. [Roteiro de Leitura Pré-Alteração](#-roteiro-de-leitura-pré-alteração)
2. [Criação e Gestão de Issues do GitHub](#-criação-e-gestão-de-issues-do-github)
3. [Esteira de Desenvolvimento e Implementação](#-esteira-de-desenvolvimento-e-implementação)
4. [Validação e Execução de Auditorias de QA](#-validação-e-execução-de-auditorias-de-qa)
5. [Processo de Testes e Homologação](#-processo-de-testes-e-homologação)
6. [Esteira de Release e Publicação](#-esteira-de-release-e-publicação)
7. [Atualização da Memória ao Fim de Sessão](#-atualização-da-memória-ao-fim-de-sessão)
8. [Documentos relacionados](#-documentos-relacionados)

---

## 🧭 Roteiro de Leitura Pré-Alteração

Antes de alterar qualquer arquivo de código ou documentação, o desenvolvedor deve ler os seguintes documentos do repositório para carregar o contexto de arquitetura e regras de negócio:

1.  **[project-status.md](../../.agents/memory/project-status.md) (Primeira Leitura):** Entenda o snapshot geral de status, versão estável ativa e progresso de QA do projeto.
2.  **[DEVELOPMENT_WORKFLOW.md](./DEVELOPMENT_WORKFLOW.md):** Revise as regras de escrita de código WordPress (WPCS) e a matriz de DoD (Definition of Done) para aprovação.
3.  **[ARCHITECTURE.md](./ARCHITECTURE.md):** Compreenda as dependências de componentes, regras de injeção de imagens e SEO, transients e segurança por diagramas Mermaid.
4.  **[DECISIONS.md](./DECISIONS.md):** Inspecione as 8 decisões arquiteturais (ADRs) vigentes para evitar quebrar premissas de refatorações anteriores.

---

## 📋 Criação e Gestão de Issues do GitHub

Qualquer evolução ou manutenção deve ser rastreada formalmente no GitHub:
*   **Issues do GitHub:** Crie uma nova Issue mapeando com precisão a finalidade e o escopo da tarefa.
*   **Evite Tarefas Livres:** Nenhuma linha de código deve ser modificada ou commitada sem uma Issue correspondente de suporte.

---

## 💻 Esteira de Desenvolvimento e Implementação

1.  **Criação de Branch:** A partir da branch principal `main`, crie uma branch de tarefa:
    *   Para novas funcionalidades: `git checkout -b feature/[task-slug]`
    *   Para correções: `git checkout -b fix/[bug-slug]`
2.  **Separação de Assets (SoC):** Garanta que estilos novos sejam inseridos em [admin.css](../assets/css/admin.css) e scripts lógicos em [admin.js](../assets/js/admin.js). Arquivos inline são proibidos.
3.  **Prefixação e Escopo:** Verifique se novas funções, variáveis, hooks e seletores utilizam estritamente o prefixo de escopo `gpg_`.

---

## 🚦 Validação e Execução de Auditorias de QA

Antes de mesclar ramificações ou commitar alterações, execute o validador de consistência local para auditar a segurança e conformidade sintática:

```bash
# Executa a validação de segurança e qualidade local
python .agents/scripts/checklist.py .
```

*Nota: Resolva quaisquer erros críticos apontados de Nonces, permissões ou SSRF antes de avançar.*

---

## 🧪 Processo de Testes e Homologação

1.  **Test Plan:** Caso a alteração altere o comportamento lógico do plugin, desenhe um plano de testes funcionais atualizando o roteiro de testes.
2.  **Bypass Seguro para Homologação:** É autorizada a utilização local do arquivo `autologin.php` na raiz pública para contornar telas de login do LocalWP durante testes repetitivos de AJAX. Esse arquivo deve ser mantido restrito ao ambiente local e excluído do versionamento via `.gitignore`.
3.  **Rastreabilidade de Evidências:** Registre os sucessos, capturas de logs PHP de debug e prints de validação no relatório de homologação de desenvolvimento correspondente.

---

## 🚀 Esteira de Release e Publicação

Ao preparar o lançamento de uma nova release de versão:
1.  **Sincronização:** Atualize a tag `Version:` no cabeçalho de [gerador-posts-gemini.php](../gerador-posts-gemini.php) e registre as alterações no [CHANGELOG.md](../CHANGELOG.md).
2.  **Tagging Semântico:** Crie a tag anotada do Git (ex: `git tag -a v1.1.0 -m "Release v1.1.0"`) e envie para o repositório remoto.
3.  **ZIP de Produção:** Compile o arquivo ZIP de distribuição comercial do plugin ([gerador-posts-gemini.zip](../../../../gerador-posts-gemini.zip)) na raiz pública. Certifique-se de que o empacotador exclua arquivos exclusivos do Git e relatórios/manuais técnicos (como a pasta `/docs` do plugin e arquivos de QA locais).
4.  **GitHub Release:** Publique a release no GitHub anexando o ZIP limpo de produção.
5.  **Referências:** Siga o procedimento exaustivo mapeado no manual [RELEASE_PROCESS.md](./RELEASE_PROCESS.md).

---

## 💾 Atualização da Memória ao Fim de Sessão

Ao final de cada turno de desenvolvimento ou após a publicação de uma Release:
*   Acesse a pasta de memória persistente dos agentes inteligência artificial em [.agents/memory/](../../.agents/memory/).
*   Atualize o snapshot de status em `project-status.md` para refletir as novas versões estáveis, releases futuras, auditorias vencidas e cobertura atual.
*   Atualize as convenções em `project-conventions.md` e registre novas decisões arquiteturais estruturadas em `tech-decisions.md` caso novas premissas tenham sido adotadas.
*   Isso garante consistência e continuidade perfeita nas próximas sessões de pair programming.

---

## 🔗 Documentos relacionados

Para navegar e aprofundar-se nos fluxos de qualidade e engenharia do Handbook, consulte:
*   **[DEVELOPMENT_WORKFLOW.md](./DEVELOPMENT_WORKFLOW.md):** Manual detalhado do fluxo de desenvolvimento e QA.
*   **[ARCHITECTURE.md](./ARCHITECTURE.md):** Mapeamento físico de componentes e diagramas Mermaid.
*   **[RELEASE_PROCESS.md](./RELEASE_PROCESS.md):** Fluxo exaustivo de empacotamento e publicação Git.
*   **[BOOTSTRAP_LOCALWP.md](./BOOTSTRAP_LOCALWP.md):** Guia de inicialização e setup do LocalWP do zero.
*   **[TROUBLESHOOTING.md](./TROUBLESHOOTING.md):** Diagnósticos de falhas operacionais e contenções.
*   **[project-status.md](../../.agents/memory/project-status.md):** Snapshot de status atual consolidado do projeto.
