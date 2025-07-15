<?php

/**
 * Plugin Name: Aurora Design Blocks 
 * Description: カスタムブロックを追加するプラグイン。
 * Version: 1.0.3
 * Author: Yurika Toshida at Aurora Lab
 * Text Domain: aurora-design-blocks
 * Update URI: https://integlight.auroralab-design.com/wp-content/updates/aurora-design-blocks.json
 */

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
