<?php
namespace GPG\AI\Providers;

use GPG\AI\Contracts\TextProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Provedor para integração com os serviços de chat e completions da OpenAI.
 */
class OpenAIProvider extends AbstractProvider implements TextProviderInterface {

	/**
	 * Gera texto estruturado via OpenAI utilizando formato JSON de objeto.
	 */
	public function generateText( $prompt, $model ) {
		$body = array(
			'model' => $model,
			'messages' => array(
				array(
					'role' => 'user',
					'content' => $prompt
				)
			),
			'response_format' => array( 'type' => 'json_object' ),
			'max_tokens' => 4096
		);

		$url = 'https://api.openai.com/v1/chat/completions';
		$headers = array(
			'Authorization' => 'Bearer ' . $this->api_key,
		);

		$response = $this->post( $url, $body, $headers );
		return $this->checkResponse( $response, 'OpenAI' );
	}
}
