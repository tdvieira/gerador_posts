# Estado do Projeto (Project Status Snapshot) — v1.0.0

Este arquivo fornece um resumo executivo unificado do estado atualizado do projeto do plugin **Gerador de Posts (IA)** após a conclusão de sua primeira release oficial. Ele funciona como a principal fonte de contexto técnico para novas sessões de desenvolvimento.

---

## 📋 Resumo Executivo do Status

| Metadado de Projeto | Registro e Estado Atual |
| :--- | :--- |
| **Nome do Projeto** | Gerador de Posts (IA) |
| **Status do Projeto** | Estável e Homologado |
| **Versão Estável** | `v1.0.0` |
| **Próxima Release** | `v1.1.0` |
| **Branch Principal** | `main` (remoto oficial apontando para GitHub) |
| **Repositório GitHub** | `https://github.com/tdvieira/gerador_posts.git` |
| **Release Oficial** | Tag `v1.0.0` e commit semântico correspondente enviados com sucesso. |
| **Developer Handbook** | Localizado em [docs/](../../docs/) |
| **Estado da Documentação**| Concluída, revisada por auditoria de qualidade e relocada (100% de links relativos portáveis). |
| **Cobertura de QA** | 100% de sucesso. Todos os 23 cenários de testes funcionais executados sem falhas abertas. |
| **Auditorias Concluídas** | Clean Code, Enhance, Security, Dead Code, Functional Testing, Release Readiness e Documentation Quality. |
| **Arquitetura** | Separação total de CSS/JS (SoC) e modularização do controlador PHP em helpers focados (SRP). |
| **Segurança** | Proteção ativa de Nonces, Capabilities (`manage_options`), SSRF (`wp_http_validate_url`) e SSL Verify dinâmico. |
| **Performance** | Cache persistente de 12 horas por Transients do WordPress com invalidação ativa via hooks. |
| **Processo de Dev** | 13 fases estritas (do Planejamento à Release de Versão) coordenadas via scripts de QA. |
| **Convenções do Projeto**| Branches baseadas em `feature/` e `fix/`, Conventional Commits e evoluções geridas por Issues. |
| **Estado Geral** | **Pronto para evolução na v1.1.0** |

---

## 📂 Documentação Oficial (Developer Handbook)

Toda a documentação corporativa de engenharia e os relatórios técnicos residem na subpasta do plugin. Siga os links relativos abaixo para acesso direto:

*   **[Workflow de Desenvolvimento](../../docs/DEVELOPMENT_WORKFLOW.md):** Processo end-to-end de engenharia, DoD (Definition of Done) e padrões de código WordPress.
*   **[Manual de Arquitetura](../../docs/ARCHITECTURE.md):** Diagramação Mermaid (componentes, geração, transients e segurança) e integrações de texto/imagem de IAs.
*   **[Manual de Processo de Release](../../docs/RELEASE_PROCESS.md):** Regras de staging, commits Git, tags SemVer, empacotamento do ZIP de produção e critérios de decisão GO/NO-GO.
*   **[Manual de Agentes e Workflows](../../docs/AGENTS.md):** Catálogo de personas especialistas do AG Kit e ordem de execução de auditorias do ciclo de QA.
*   **[Registro de Decisões Técnicas (ADR)](../../docs/DECISIONS.md):** Diário cronológico de 8 decisões de engenharia adotadas no desenvolvimento e seus impactos.
*   **[Relatório de Documentação Técnica](../../docs/technical_documentation_report.md):** Resumo executivo de métricas de páginas e inventário de manuais.
*   **[Relatório de Realocação de Manuais](../../docs/documentation_relocation_report.md):** Relatório de saneamento e migração de caminhos absolutos locais para relativos portáveis.
*   **[Relatório de Qualidade de Documentação](../../docs/documentation_quality_report.md):** Auditoria final de consistência documental e avaliação excelente concedida ao Handbook.
*   **[Relatório de Consistência de Manuais](../../docs/documentation_consistency_report.md):** Parecer técnico de remoção de premissas residuais de Milestones no Handbook.
*   **[Relatório de Consistência do Repositório](../../docs/repository_consistency_report.md):** Auditoria global em modo somente leitura atestando o alinhamento total por Issues e Releases.
*   **[Relatório de Atualização de Nomenclatura](../../docs/ui_label_update_report.md):** Registro da simplificação visual de labels do provedor Groq no admin PHP.

---

## 🎯 Leitura Recomendada para Próximas Sessões

Ao iniciar uma nova sessão de desenvolvimento ou onboarding no projeto, siga estritamente a sequência de leitura abaixo para carregar todo o contexto:

1.  **[project-status.md](./project-status.md) (Este Arquivo):** Entenda o estado executivo do projeto, o progresso de QA e os links diretos de documentação.
2.  **[MEMORY.md](./MEMORY.md) (Índice de Memória):** Compreenda a finalidade de cada arquivo de memória e o roteamento de carregamento de premissas.
3.  **Documentação em [/docs](../../docs/):** Aprofunde-se no guia de desenvolvimento ([DEVELOPMENT_WORKFLOW.md](../../docs/DEVELOPMENT_WORKFLOW.md)) e nos detalhes de design e Mermaid em ([ARCHITECTURE.md](../../docs/ARCHITECTURE.md)).
4.  **Código-Fonte:** Analise o controlador PHP principal ([gerador-posts-gemini.php](../../gerador-posts-gemini.php)) e o visual administrativo ([admin-ui.php](../../admin-ui.php)).
