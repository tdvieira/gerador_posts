# Workflow de Preparação de Release (Release Preparation Workflow)

Este workflow define as etapas lógicas genéricas para planejar, validar e empacotar uma nova versão estável (Release) de produto no controle de versão Git.

---

## 🔄 Roteiro de Preparação

1.  **Validação Final de Integridade:**
    *   Executar as ferramentas de QA e scripts locais de verificação física para garantir que a branch de staging está verde.
2.  **Saneamento de Arquivos e Assets:**
    *   Certificar que nenhum arquivo de cache de desenvolvimento, rascunhos de homologação ou credenciais privadas restaram no repositório.
    *   Assegurar a ausência de arquivos marcadores `.gitkeep` em subpastas ativas.
3.  **Determinação de Versão (SemVer):**
    *   Aplicar as regras de versionamento semântico (SemVer) no cabeçalho do arquivo principal e no `project-status.md` (ex: `v1.0.0` para major, `v1.0.1` para patch).
4.  **Tagging e Push Remoto:**
    *   Aplicar a tag correspondente da release no commit consolidado.
    *   Subir a branch estável e a tag de release para o repositório remoto.
