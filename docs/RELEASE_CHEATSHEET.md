# Cheatsheet de Releases — Guia de Referência Rápida
**Pipeline Oficial de Release — Fluxo de Duas Etapas**

---

## 🚀 Comandos Oficiais de Publicação

Execute sequencialmente os dois comandos abaixo a partir do console PowerShell na raiz do repositório:

### Etapa 1: Prepare Release
```powershell
powershell -ExecutionPolicy Bypass -File scripts/prepare_release.ps1 -Version X.Y.Z
```
*   **Descrição:** Executa a sincronização automática de versão (manifesto PHP, bootstrap e `readme.txt`), analisa a consistência estrutural, varre a pasta `docs/releases/` para coletar dinamicamente e consolidar as Release Notes das seções `## Resumo para Release` no `CHANGELOG.md`, gera o pacote ZIP e roda a auditoria técnica estrutural de integridade para o WordPress.

### Etapa 2: Publish Release
```powershell
powershell -ExecutionPolicy Bypass -File scripts/publish_release.ps1
```
*   **Descrição:** Realiza a auditoria de integridade da Working Tree (baseada em categorias arquiteturais carregadas do JSON externo de configuração), faz o commit administrativo, gera a tag Git anotada local, envia commits e tags para o repositório remoto, extrai dinamicamente o bloco da versão corrente no `CHANGELOG.md` e publica a GitHub Release com upload do pacote ZIP.

---

## 🚦 Observação Importante para Operadores

> [!IMPORTANT]
> **Fluxo Operacional Estrito:** O processo de publicação de novas releases é composto exclusivamente pelos dois comandos acima. Qualquer etapa ou comando adicional (como invocações isoladas de `scripts/build_release.ps1`) caracteriza manutenção interna ou teste da esteira, e **não** faz parte do fluxo operacional oficial da release.
