<?php
namespace GPG\AI\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Interface que define as operações para os provedores de IA de geração de imagens.
 */
interface ImageProviderInterface {

	/**
	 * Executa a chamada à API correspondente para gerar uma imagem.
	 *
	 * @param string $prompt Prompt de descrição visual.
	 * @param string $model Nome do modelo técnico (ex: dall-e-3).
	 * @return array|\WP_Error Resposta HTTP bruta do WordPress ou erro controlável.
	 */
	public function generateImage( $prompt, $model );
}
