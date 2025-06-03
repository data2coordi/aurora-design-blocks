<?php

/**
 * Plugin Name: Aurora Design Blocks 
 * Description: カスタムブロックを追加するプラグイン。
 * Version: 1.0
 * Author: Yurika Toshida at Aurora Lab
 * Text Domain: aurora-design-blocks
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




function aurora_design_blocks_enqueue_styles()
{

    // CSS を登録して読み込む
    wp_enqueue_style('aurora-design-blocks-style-block-module', AURORA_DESIGN_BLOCKS_URL . 'css/block-module.css', array(), _S_VERSION, 'all');
}
add_action('wp_enqueue_scripts', 'aurora_design_blocks_enqueue_styles');


function aurora_design_enqueue_styles()
{

    // CSS を登録して読み込む
    wp_enqueue_style('aurora-design-style-module', AURORA_DESIGN_BLOCKS_URL . 'css/aurora-design.css', array(), _S_VERSION, 'all');
}
add_action('wp_enqueue_scripts', 'aurora_design_enqueue_styles');


function aurora_design_blocks_load_textdomain()
{
    $loaded = load_plugin_textdomain(
        'aurora-design-blocks', // テキストドメイン
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('init', 'aurora_design_blocks_load_textdomain');






require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-outerAssets.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-customizer.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-awesome.php';
