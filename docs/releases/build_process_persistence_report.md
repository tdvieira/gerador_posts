# Relatório de Persistência do Processo de Build — v1.0.0

Este relatório descreve as ações executadas para a persistência física, versionamento e documentação operacional do fluxo de empacotamento automatizado do plugin **Gerador de Posts (IA)**.

---

## 📁 1. Arquivos Criados e Modificados

As seguintes alterações foram integradas ao repositório:

1.  **[scripts/build_release.ps1](scripts/build_release.ps1) (Criado):** Script em PowerShell versionado e definitivo de build. Detecta o diretório do plugin de forma 100% dinâmica e independente de sistema operacional, eliminando dependências locais absolutas.
2.  **[README.md](README.md) (Modificado):** Atualizada a seção "Release" incluindo a subseção "Geração de Build Local" e o respectivo comando PowerShell de execução.
3.  **[docs/releases/RELEASE_PROCESS.md](docs/releases/RELEASE_PROCESS.md) (Modificado):** Atualizado o manual de release para guiar os engenheiros sobre a execução oficial do script em `scripts/` durante a montagem de novos ZIPs.
4.  **[.agents/rules/project-governance.md](.agents/rules/project-governance.md) (Modificado):** Inserida a regra permanente (Princípio 16) exigindo que todas as ferramentas e scripts de build de releases residam fisicamente no repositório de forma documentada.

---

## ⚙️ 2. Fluxo Oficial de Build e Empacotamento

Qualquer desenvolvedor do projeto pode gerar a build oficial executando o comando no console na raiz do plugin:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/build_release.ps1
```

### Sequência Operacional do Script
1.  **Deteção Dinâmica:** O script lê o caminho absoluto do arquivo PowerShell e descobre o diretório raiz do plugin.
2.  **Preparação de Sandbox:** Cria um diretório temporário no sistema, gerando a subpasta `/gerador-posts-gemini/`.
3.  **Cópia de Produção:** Copia todos os arquivos funcionais e de governança mínimos para a sandbox.
4.  **Exclusão de Suporte:** Executa a purga de arquivos `.gitkeep` sob os subdiretórios de produção.
5.  **Geração ZIP (.NET):** Invoca a biblioteca `.NET ZipFile` sobre a pasta temporária, criando a distribuição física sob `build/gerador-posts-gemini.zip` e normalizando caminhos internos com barras normais `/`.

---

## 📝 3. Validações Realizadas

*   **Validação de Caminhos do Script:** O script foi executado de forma portátil a partir da nova pasta `/scripts/` e gerou a build de forma autônoma sem regressões.
*   **Validação do ZIP de Saída:** O arquivo compactado gerado foi auditado com sucesso no interpretador Python, atestando a presença da pasta `/gerador-posts-gemini/` e do cabeçalho `gerador-posts-gemini.php` diretamente sob a raiz interna com separador `/`.

O processo de build está **homologado** e persistido no repositório.

---

## 🚦 Bloco de Status do Relatório
*   **Status:** Concluído
*   **Fase:** Persistência do Processo de Build e scripts/
*   **Resultado:** Aprovado (Script scripts/build_release.ps1 Criado, README.md e Governança Atualizados)
*   **Validação:** Execução Portátil do Script scripts/build_release.ps1, Validação Cruzada de Entradas do ZIP e Auditoria de Regras no project-governance.md
