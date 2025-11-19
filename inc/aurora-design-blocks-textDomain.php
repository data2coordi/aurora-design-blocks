<?php

/********************************************************************/
/* 国際化 s */
/********************************************************************/
/**
 * Conditional textdomain load that matches WordPress.org expectations
 * and still supports GitHub/local builds.
 */

// 例: プラグインのメインファイルで定義している想定
// define( 'AURORA_DESIGN_BLOCKS_PATH', plugin_dir_path( __FILE__ ) );
// define( 'AURORA_DESIGN_BLOCKS_FILE', __FILE__ ); // main plugin file

function aurora_design_blocks_load_textdomain()
{
    $domain = 'aurora-design-blocks';

    // 1) WP_LANG_DIR にある .mo を探す（例: wp-content/languages/plugins/aurora-design-blocks-ja.mo）
    // determine_locale() は WordPress 4.7+ 用の推奨関数
    if (function_exists('determine_locale')) {
        $locale = determine_locale();
    } else {
        $locale = get_locale();
    }

    $wp_lang_mo = WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo';

    if (is_readable($wp_lang_mo)) {
        // 直接読み込む（存在する場合のみ）
        load_textdomain($domain, $wp_lang_mo);
        return;
    }

    // 2) 公式の自動ロード済みかを期待（多くの場合ここで何もしなくてOK）
    // ただし自動ロードされていなければプラグイン内の languages/ をフォールバックする
    // ここで重要なのは「プラグインのルートを基準にした相対パス」を渡すこと
    // プラグインフォルダ名（例: aurora-design-blocks）
    $plugin_folder = basename(rtrim(AURORA_DESIGN_BLOCKS_PATH, '/'));
    $relative = $plugin_folder . '/languages';
    load_plugin_textdomain($domain, false, $relative);
    // 万が一定数未定義なら最終手段：プラグインメインファイルの basename を使う（要注意）
}
add_action('plugins_loaded', 'aurora_design_blocks_load_textdomain');
/********************************************************************/
/* 国際化 e */
/********************************************************************/
