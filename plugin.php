<?php

/**
 * Plugin Name: Aurora Design Blocks 
 * Description: カスタムブロックを追加するプラグイン。
 * Version: 1.0.3
 * Author: Yurika Toshida at Aurora Lab
 * Text Domain: aurora-design-blocks
 * GitHub Plugin URI: https://github.com/data2coordi/aurora-design-blocks
 * GitHub Release Asset: true
 * Primary Branch: master
 */

// PSR-4 オートローダー
spl_autoload_register(function ($class) {
    $prefix = 'Fragen\\Git_Updater\\';
    $base_dir = __DIR__ . '/third-party/Git_Updater/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relative_class = substr($class, strlen($prefix));
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Bootstrapクラスを読み込み（オートローダーで読めるため require は任意）
require_once __DIR__ . '/third-party/Git_Updater/Bootstrap.php';

// プラグイン初期化
add_action('plugins_loaded', function () {
    if (class_exists('\Fragen\Git_Updater\Bootstrap')) {
        new \Fragen\Git_Updater\Bootstrap();
    }
});

//var_dump(__DIR__ . '/third-party/Git_Updater/Bootstrap.php');

if (!defined('_S_VERSION')) {
    // Replace the version number of the theme on each release.
    define('_S_VERSION', '1.0.1');
}



if (!defined('ABSPATH')) {
    exit; // 直接アクセスを防ぐ
}

define('AURORA_DESIGN_BLOCKS_PATH', plugin_dir_path(__FILE__));
define('AURORA_DESIGN_BLOCKS_URL', plugin_dir_url(__FILE__));









require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-outerAssets.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-forBlocks.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-customizer.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-awesome.php';
