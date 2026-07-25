<?php
namespace GPG\AI\Providers;

use GPG\AI\Contracts\ImageProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Provedor para geração de imagens utilizando o modelo DALL-E da OpenAI.
 */
class DallEProvider extends AbstractProvider implements ImageProviderInterface {

	/**
	 * Gera imagem via OpenAI DALL-E em resolução widescreen.
	 */
	public function generateImage( $prompt, $model ) {
		$size = '1792x1024'; // Resolução padrão widescreen para suporte a cortes de design do plugin
		$body = array(
			'model'  => $model,
			'prompt' => $prompt,
			'n'      => 1,
			'size'   => $size
		);

		$url = 'https://api.openai.com/v1/images/generations';
		$headers = array(
			'Authorization' => 'Bearer ' . $this->api_key,
		);

		$response = $this->post( $url, $body, $headers );
		return $this->checkResponse( $response, 'OpenAI DALL-E' );
	}
}
