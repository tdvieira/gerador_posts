<?php
namespace GPG\AI\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Interface que define as operações para os provedores de IA de geração de texto.
 */
interface TextProviderInterface {

	/**
	 * Executa a chamada à API correspondente para gerar o post.
	 *
	 * @param string $prompt Prompt de instruções para a IA.
	 * @param string $model Nome do modelo técnico (ex: gemini-1.5-flash).
	 * @return array|\WP_Error Resposta HTTP bruta do WordPress ou erro controlável.
	 */
	public function generateText( $prompt, $model );
}
