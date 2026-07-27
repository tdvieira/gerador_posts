=== Gerador de Posts ===
Contributors: tdvieira
Tags: post generator, ai writer, gemini, openai, auto post
Requires at least: 5.8
Tested up to: 7.0.2
Requires PHP: 8.0
Stable tag: 2.0.4
License: Proprietary
License URI: LICENSE

Cria posts estruturados com sumários (TOC), gera imagens widescreen, integra com o Rank Math SEO e realiza agendamento de publicações em lote usando Inteligências Artificiais.

== Description ==

O **Gerador de Posts (IA)** é um plugin WordPress de nível profissional projetado para automatizar a criação, otimização e publicação de artigos de alta fidelidade visual e estrutural utilizando múltiplos provedores de Inteligência Artificial.

= Principais Funcionalidades =
* **Orquestração de Múltiplas APIs de IA:** Suporte nativo para Google Gemini, OpenAI e Groq Cloud.
* **Geração de Imagens:** Criação de imagens widescreen (16:9) utilizando DALL-E 3, Google Imagen 4 e Flux-Anime.
* **Processamento de Mídia:** Conversão automática de imagens para WebP com qualidade otimizada e crop para monitores Retina.
* **Otimização SEO:** Injeção automática das palavras-chave de foco e metadados compatíveis com o Rank Math SEO.
* **Agendador em Lote:** Criação em sequência de múltiplos artigos a partir de uma lista de temas.

== Installation ==

1. Faça o upload do arquivo ZIP do plugin através do painel do WordPress em **Plugins > Adicionar Novo**.
2. Ative o plugin.
3. Acesse **Posts > Gerador de Posts** no menu lateral.
4. Vá até a aba **Configurações** e insira as suas chaves de API (Gemini, OpenAI, Groq).

== Frequently Asked Questions ==

= O Rank Math SEO é obrigatório? =
Não, o plugin funciona de forma autônoma. A integração com o Rank Math SEO é um recurso opcional para injeção automática de metadados quando o plugin estiver ativo.

= Quais os formatos de imagem gerados? =
O plugin converte todas as imagens baixadas das APIs externas para o formato otimizado WebP e realiza o crop para resoluções compatíveis com telas Retina (1408px de largura).

== Changelog ==

= 2.0.3 =
* Implementação da whitelist dinâmica para relatórios de deploy na pipeline de release.
* Limpeza e otimização do script de deploy local.

= 2.0.0 =
* Versão inicial estável com suporte a múltiplos provedores de IA e geração em lote.

== Upgrade Notice ==

= 2.0.3 =
Atualização recomendada para conformidade estrutural com o ecossistema WordPress.
