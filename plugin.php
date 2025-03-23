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




function aurora_design_blocks_enqueue_styles()
{

    // CSS を登録して読み込む
    wp_enqueue_style('aurora-design-blocks-style-block-module', plugin_dir_url(__FILE__) . 'css/block-module.css', array(), _S_VERSION, 'all');
    wp_enqueue_style('aurora-design-blocks-style-awesome-all', plugin_dir_url(__FILE__) . 'css/awesome-all.min.css', array(), _S_VERSION, 'all');
}
add_action('wp_enqueue_scripts', 'aurora_design_blocks_enqueue_styles');


function aurora_design_blocks_load_textdomain()
{
    $loaded = load_plugin_textdomain(
        'aurora-design-blocks', // テキストドメイン
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('init', 'aurora_design_blocks_load_textdomain');


/*
function aurora_design_blocks_debug_textdomain_var_dump()
{
    echo '<pre>';

    // 現在のロケールを取得して出力
    $locale = get_locale();
    var_dump('Current locale: ' . $locale);

    // MOファイルの絶対パスを生成して存在チェック
    $mo_file = dirname(plugin_basename(__FILE__)) . '/languages/aurora-design-blocks-' . $locale . '.mo';
    if (file_exists($mo_file)) {
        var_dump("MOファイルが見つかりました: $mo_file");
    } else {
        var_dump("MOファイルが見つかりません: $mo_file");
    }

    // 翻訳対象の文字列の翻訳結果を出力
    $translated = __('Hello World Block', 'aurora-design-blocks');
    var_dump("Translated 'Hello World Block': " . $translated);

    echo '</pre>';
}
// 優先度を 20 など、load_plugin_textdomain() より後に実行する
add_action('init', 'aurora_design_blocks_debug_textdomain_var_dump', 20);


function aurora_design_blocks_debug_get_translations()
{
    $translations = get_translations_for_domain('aurora-design-blocks');
    echo '<pre>';
    //var_dump($translations);
    echo '</pre>';
}
add_action('init', 'aurora_design_blocks_debug_get_translations', 30);
*/




require plugin_dir_path(__FILE__) . '/inc/aurora-design-blocks-outerAssets.php';
require plugin_dir_path(__FILE__) . '/inc/aurora-design-blocks.php';
