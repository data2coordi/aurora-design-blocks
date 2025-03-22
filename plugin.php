<?php

/**
 * Plugin Name: Aurora Design Blocks 
 * Description: カスタムブロックを追加するプラグイン。
 * Version: 1.0
 * Author: Yurika Toshida at Aurora Lab
 */

if (!defined('_S_VERSION')) {
    // Replace the version number of the theme on each release.
    define('_S_VERSION', '1.0.1');
}



if (!defined('ABSPATH')) {
    exit; // 直接アクセスを防ぐ
}

// ブロック登録処理を追加
function register_custom_blocks()
{
    $blocks = glob(plugin_dir_path(__FILE__) . 'blocks/*', GLOB_ONLYDIR);
    foreach ($blocks as $block) {
        if (file_exists($block . '/block.json')) {
            register_block_type($block);
        }
    }
}
add_action('init', 'register_custom_blocks');


function aurora_design_blocks_enqueue_styles()
{
    // プラグインの URL から CSS のパスを取得
    $css_url = plugin_dir_url(__FILE__) . 'css/block-module.css';

    // CSS を登録して読み込む
    wp_enqueue_style('aurora-design-blocks-style', $css_url, array(), '1.0.0', 'all');
}
add_action('wp_enqueue_scripts', 'aurora_design_blocks_enqueue_styles');


require plugin_dir_path(__FILE__) . '/inc/aurora-design-blocks-outerAssets.php';
require plugin_dir_path(__FILE__) . 'inc/aurora-design-blocks.php';
