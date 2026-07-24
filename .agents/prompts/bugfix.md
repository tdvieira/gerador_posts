# Diagnóstico e Correção de Falhas (Bugfix Prompt)

Este prompt genérico define o modelo para investigar falhas operacionais, identificar a causa raiz e elaborar planos de correção incremental de bugs no repositório.

---

## 🛠️ Procedimento de Resolução de Defeitos

*   **Reprodução e Causa Raiz:** Mapear os passos exatos para reproduzir a falha física ou sintática e determinar a causa raiz.
*   **Mapeamento de Impacto (Blast Radius):** Avaliar quais dependências de arquivos ou módulos do plugin podem ser afetadas pela correção proposta.
*   **Correção sob SRP:** Desenhar a solução aplicando a mínima intervenção necessária para corrigir o bug de forma cirúrgica.
*   **Testes de Não-Regressão:** Executar validações para garantir que a correção não inseriu novos defeitos em outras partes do sistema.
