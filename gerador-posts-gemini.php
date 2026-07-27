<?php
/**
 * Plugin Name: Gerador de Posts
 * Description: Cria posts estruturados seguindo o padrão do blog, gera até 2 imagens 16:9, vincula SEO (Rank Math) e agenda a publicação em lote.
 * Version: 2.0.5
 * Author: Thiago Vieira
 * Text Domain: gerador-posts-gemini
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Inicialização do atualizador de atualizações do plugin
if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/updater.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/updater.php';
} else {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Arquivo includes/updater.php não encontrado.', 'gerador-posts-gemini' ) . '</p></div>';
	} );
}

// Registrar Autoloader PSR-4 para o namespace oficial GPG
spl_autoload_register( function ( $class ) {
	$prefix = 'GPG\\';
	$base_dir = __DIR__ . '/includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

// Inicializar ciclo de vida e bootstrap do plugin
\GPG\Core\PluginBootstrap::boot();
