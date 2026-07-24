# Geração de Releases do Plugin (Plugin Release Prompt)

Este prompt apoia a compilação final da release comercial e a criação de tags no repositório.

---

## 📦 Diretrizes de Release

*   **Bump de Versão:** Atualizar a versão SemVer no controlador PHP principal.
*   **Limpeza e ZIP:** Remover mídias, caches locais e scripts utilitários do zip gerado.
*   **Git Commits:** Gerar a mensagem semântica `release(vX.Y.Z): bump version...` e aplicar a tag correspondente.
