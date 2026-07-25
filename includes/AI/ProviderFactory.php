<?php
namespace GPG\AI;

use GPG\Core\Config;
use GPG\AI\Providers\GeminiProvider;
use GPG\AI\Providers\OpenAIProvider;
use GPG\AI\Providers\GroqProvider;
use GPG\AI\Providers\DallEProvider;
use GPG\AI\Providers\GoogleImagenProvider;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Fábrica para instanciar dinamicamente os provedores de texto e imagem baseados nas configurações.
 */
class ProviderFactory {

	/**
	 * Instancia o provedor de texto adequado.
	 *
	 * @param string $provider Nome do provedor ('gemini', 'openai', 'groq').
	 * @return \GPG\AI\Contracts\TextProviderInterface|\WP_Error
	 */
	public static function createTextProvider( $provider ) {
		switch ( strtolower( $provider ) ) {
			case 'openai':
				$key = Config::getOpenAiKey();
				if ( empty( $key ) ) {
					return new \WP_Error( 'gpg_missing_api_key', __( 'Chave da API OpenAI não configurada.', 'gerador-posts-gemini' ) );
				}
				return new OpenAIProvider( $key );

			case 'groq':
				$key = Config::getGroqKey();
				if ( empty( $key ) ) {
					return new \WP_Error( 'gpg_missing_api_key', __( 'Chave da API Groq não configurada.', 'gerador-posts-gemini' ) );
				}
				return new GroqProvider( $key );

			case 'gemini':
			default:
				$key = Config::getGeminiKey();
				if ( empty( $key ) ) {
					return new \WP_Error( 'gpg_missing_api_key', __( 'Chave da API Gemini não configurada.', 'gerador-posts-gemini' ) );
				}
				return new GeminiProvider( $key );
		}
	}

	/**
	 * Instancia o provedor de imagens adequado.
	 *
	 * @param string $provider Nome do provedor ('gemini', 'openai').
	 * @return \GPG\AI\Contracts\ImageProviderInterface|\WP_Error
	 */
	public static function createImageProvider( $provider ) {
		switch ( strtolower( $provider ) ) {
			case 'openai':
				$key = Config::getOpenAiKey();
				if ( empty( $key ) ) {
					return new \WP_Error( 'gpg_missing_api_key', __( 'Chave da API OpenAI não configurada.', 'gerador-posts-gemini' ) );
				}
				return new DallEProvider( $key );

			case 'gemini':
			default:
				$key = Config::getGeminiKey();
				if ( empty( $key ) ) {
					return new \WP_Error( 'gpg_missing_api_key', __( 'Chave da API Gemini não configurada.', 'gerador-posts-gemini' ) );
				}
				return new GoogleImagenProvider( $key );
		}
	}
}
