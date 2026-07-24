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
    'https://github.com/tdvieira/gerador_posts',
    dirname(__DIR__) . '/gerador-posts-gemini.php',
    'gerador-posts-gemini'
);

$updateChecker->getVcsApi()->enableReleaseAssets();