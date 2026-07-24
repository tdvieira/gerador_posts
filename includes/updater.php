<?php

if (!defined('ABSPATH')) {
    exit;
}

$puc = plugin_dir_path(__DIR__) . 'vendor/plugin-update-checker/plugin-update-checker.php';

if (!file_exists($puc)) {
    return;
}

require_once $puc;

add_action('admin_notices', function () {
    echo '<div class="notice notice-success"><p>Updater carregado com sucesso.</p></div>';
});

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/tdvieira/gerador_posts/',
    dirname(__DIR__) . '/gerador-posts-gemini.php',
    'gerador-posts-gemini'
);

// Branch estável
$updateChecker->setBranch('main');

// Utilizar o ZIP anexado à Release
$updateChecker->getVcsApi()->enableReleaseAssets('/\.zip$/i');

add_action('admin_init', function () use ($updateChecker) {
    delete_site_transient('update_plugins');
    $updateChecker->checkForUpdates();
});

// add_action('admin_init', function () use ($updateChecker) {
//     if (!current_user_can('manage_options')) {
//         return;
//     }

//     $updateChecker->checkForUpdates();

//     echo '<pre>';
//     var_dump($updateChecker->getUpdateState());
//     echo '</pre>';
//     exit;
// });