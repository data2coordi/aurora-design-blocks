<?php

/**
 * Plugin Name: Aurora Design Blocks 
 * Description: カスタムブロックを追加するプラグイン。
 * Version: 1.0.29
 * Author: Yurika Toshida at Aurora Lab
 * Text Domain: aurora-design-blocks
 */

if (!defined('_AuroraDesignBlocks_S_VERSION')) {
    // Replace the version number of the theme on each release.
    define('_AuroraDesignBlocks_S_VERSION', '1.0.29');
}



if (!defined('ABSPATH')) {
    exit; // 直接アクセスを防ぐ
}

define('AURORA_DESIGN_BLOCKS_PATH', plugin_dir_path(__FILE__));
define('AURORA_DESIGN_BLOCKS_URL', plugin_dir_url(__FILE__));

require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-outerAssets.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-customizer.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-auroraDesignBlocks.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks_helper.php';

if (file_exists(AURORA_DESIGN_BLOCKS_PATH . "/aurora-design-blocks-pro.php")) {
    require AURORA_DESIGN_BLOCKS_PATH . '/aurora-design-blocks-pro.php';
}
