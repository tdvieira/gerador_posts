<?php
namespace GPG\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Classe responsável por centralizar o acesso às configurações e constantes do plugin.
 */
class Config {

	/**
	 * Retorna o valor de uma opção do plugin.
	 *
	 * @param string $key Chave da opção (sem prefixo gpg_).
	 * @param mixed  $default Valor padrão de retorno.
	 * @return mixed
	 */
	public static function get( $key, $default = false ) {
		$option_name = 'gpg_' . $key;
		return get_option( $option_name, $default );
	}

	/**
	 * Retorna a chave de API do OpenAI.
	 *
	 * @return string|false
	 */
	public static function getOpenAiKey() {
		return self::get( 'openai_api_key' );
	}

	/**
	 * Retorna a chave de API do Gemini.
	 *
	 * @return string|false
	 */
	public static function getGeminiKey() {
		return self::get( 'gemini_api_key' );
	}

	/**
	 * Retorna a chave de API do Groq.
	 *
	 * @return string|false
	 */
	public static function getGroqKey() {
		return self::get( 'groq_api_key' );
	}

	/**
	 * Grava ou atualiza uma configuração do plugin.
	 *
	 * @param string $key Chave da opção (sem prefixo gpg_).
	 * @param mixed  $value Valor a ser gravado.
	 * @return bool
	 */
	public static function set( $key, $value ) {
		$option_name = 'gpg_' . $key;
		return update_option( $option_name, $value );
	}
}
