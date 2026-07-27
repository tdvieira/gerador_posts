<?php

if (!defined('ABSPATH')) {
    exit;
}

$puc = plugin_dir_path(__DIR__) . 'vendor/plugin-update-checker/plugin-update-checker.php';

if (!file_exists($puc)) {
    return;
}

require_once $puc;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/tdvieira/gerador_posts/',
    dirname(__DIR__) . '/gerador-posts-gemini.php',
    'gerador-posts-gemini'
);

// Branch estavel
$updateChecker->setBranch('main');

// Definir readme.txt como fonte oficial de metadados para a janela "Ver detalhes"
$updateChecker->getVcsApi()->setReadmeFilename('readme.txt');

// Utilizar o ZIP anexado a Release
$updateChecker->getVcsApi()->enableReleaseAssets('/\.zip$/i');

add_action('admin_init', function () use ($updateChecker) {
    $updateChecker->checkForUpdates();
});