<?php

/**
 * 初回だけ theme_mod の値を option に移行する。
 */
function aurora_design_blocks_migrate_theme_mods_to_options_once()
{
    // 管理画面でのみ実行
    if (! is_admin()) {
        return;
    }

    // すでに移行済みかチェック
    if (get_option('aurora_design_blocks_options_migrated')) {
        return;
    }

    $target_options = [
        'auroraDesignBlocks_ga_trackingCode',
        'auroraDesignBlocks_gtm_trackingCode',
        'auroraDesignBlocks_gtm_noscriptCode',
        'auroraDesignBlocks_adsense_code',
    ];

    foreach ($target_options as $option_name) {
        $option_value = get_option($option_name, '');
        if ($option_value === '') {
            $theme_mod_value = get_theme_mod($option_name);
            if ($theme_mod_value !== null && $theme_mod_value !== '') {
                update_option($option_name, $theme_mod_value);
                update_option('aurora_design_blocks_options_migrated', true);
            }
        }
    }

    // フラグを保存して2回目以降はスキップ

}
add_action('after_setup_theme', 'aurora_design_blocks_migrate_theme_mods_to_options_once');




/*
Plugin Name: My GTag LCP Trigger Plugin
Description: Dynamically loads gtag.js after LCP is measured.
Version: 1.0
*/

/*
Plugin Name: My GTag Defer Plugin
Description: Adds defer attribute to gtag.js script for performance.
Version: 1.0
Author: Your Name
*/

/*
Plugin Name: My GTag On User Interaction Plugin
Description: Dynamically loads gtag.js on the first user interaction to improve LCP.
Version: 1.0
Author: Your Name
*/

// フッターにスクリプトを追加
add_action('wp_footer', 'my_gtag_on_user_interaction_script');

function my_gtag_on_user_interaction_script()
{
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var gtagLoaded = false;

            function loadGtag() {
                // ロード済みなら何もしない
                if (gtagLoaded) return;

                // gtagの公式スニペットを動的に実行
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());
                gtag('config', 'G-K4EYQCDK25');

                // gtag.jsスクリプトを動的に作成・追加
                var script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=G-K4EYQCDK25';
                document.head.appendChild(script);

                gtagLoaded = true;

                // 実行された後は、リスナーを削除してメモリを解放
                ['scroll', 'mousemove', 'click', 'touchstart', 'keydown'].forEach(function(event) {
                    window.removeEventListener(event, loadGtag);
                });
                window.removeEventListener('load', loadGtag);
            }

            // ユーザーが最初の操作をしたときにgtagをロード
            ['scroll', 'mousemove', 'click', 'touchstart', 'keydown'].forEach(function(event) {
                window.addEventListener(event, loadGtag, {
                    once: true
                });
            });

            // 保険：ページの完全ロード時に実行
            window.addEventListener('load', loadGtag, {
                once: true
            });
        });
    </script>
<?php
}
