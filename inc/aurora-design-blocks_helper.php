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


///////////////////////////////////////////
///////////////////////////////////////////
///////////////////////////////////////////
///////////////////////////////////////////
///////////////////////////////////////////
//GTAG遅延ロード s

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
//add_action('wp_footer', 'my_gtag_on_user_interaction_script');

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
                //    ['scroll', 'mousemove', 'click', 'touchstart', 'keydown'].forEach(function(event) {
                //       window.removeEventListener(event, loadGtag);
                //  });
                // window.removeEventListener('load', loadGtag);
            }

            // ユーザーが最初の操作をしたときにgtagをロード
            // ['scroll', 'mousemove', 'click', 'touchstart', 'keydown'].forEach(function(event) {
            //     window.addEventListener(event, loadGtag, {
            //         once: true
            //     });
            // });

            // 保険：ページの完全ロード時に実行
            window.addEventListener('load', loadGtag, {
                once: true
            });
        });
    </script>
<?php
}


//add_action('wp_footer', 'my_inline_gtag_script');
function my_inline_gtag_script()
{
?>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        // LCPが完了したと判断できるタイミングで本体を読み込む
        window.addEventListener('load', function() {
            gtag('js', new Date());
            gtag('config', 'G-K4EYQCDK25');
            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=G-K4EYQCDK25';
            //document.head.appendChild(script);
            document.body.appendChild(script);
        }, {
            once: true
        });
    </script>
<?php
}


//add_action('wp_footer', 'my_gtag_optimized_script');

function my_gtag_optimized_script()
{
?>
    <script>
        // ドキュメントが完全に読み込まれた後に実行
        document.addEventListener('DOMContentLoaded', function() {
            // dataLayerを初期化
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }

            // 2秒の遅延後にgtag.jsスクリプトを読み込む
            setTimeout(function() {
                // gtag.jsのスクリプトタグを動的に作成
                var script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=G-K4EYQCDK25';

                // スクリプトをHTMLの<head>に追加
                document.head.appendChild(script);

                // gtagの初期設定コマンドをキューにプッシュ
                gtag('js', new Date());
                gtag('config', 'G-K4EYQCDK25');

            }, 4000); // 2000ミリ秒 = 2秒
        });
    </script>
<?php
}






//add_action('wp_footer', 'my_gtag_optimized_script2');

function my_gtag_optimized_script2()
{
?>
    <script>
        // ページロード時にデータレイヤーとgtag関数を定義
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        // ユーザーが操作した時、または5秒後にgtag.js本体を読み込む
        var gtagLoaded = false;
        var loadGtag = function() {
            if (gtagLoaded) return;
            gtagLoaded = true;

            // gtag.jsスクリプトを動的に作成・追加
            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=G-K4EYQCDK25';
            document.head.appendChild(script);

            // gtagの初期設定コマンドをキューにプッシュ
            gtag('js', new Date());
            gtag('config', 'G-K4EYQCDK25');
        };

        // ユーザーインタラクションを監視
        ['scroll', 'mousemove', 'click', 'touchstart', 'keydown'].forEach(function(event) {
            window.addEventListener(event, loadGtag, {
                once: true
            });
        });

        // 保険：ユーザーインタラクションがない場合でも5秒後に実行
        setTimeout(loadGtag, 4000);
    </script>
<?php
}



//add_action('wp_footer', 'my_gtag_optimized_idle_script3');

function my_gtag_optimized_idle_script3()
{
?>
    <script>
        // gtag関数とdataLayerを初期化
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        // GA4のスクリプトを読み込む関数
        var loadGtag = function() {
            // gtag.jsスクリプトを動的に作成・追加
            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=G-K4EYQCDK25';
            document.head.appendChild(script);

            // gtagの初期設定コマンドをキューにプッシュ
            gtag('js', new Date());
            gtag('config', 'G-K4EYQCDK25');
        };

        // ブラウザがrequestIdleCallbackをサポートしているか確認
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(loadGtag);
        } else {
            // サポートしていない場合は、フォールバックとしてsetTimeoutを使用
            // LCPが完了したと見なせるタイミングまで遅延
            setTimeout(loadGtag, 4000);
        }
    </script>
<?php
}




add_action('wp_footer', 'my_gtag_optimized_idle_interaction_script');

function my_gtag_optimized_idle_interaction_script()
{
?>
    <script>
        // gtag関数とdataLayerを初期化
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        var gtagLoaded = false;

        // GA4のスクリプトを読み込む関数
        var loadGtag = function() {
            if (gtagLoaded) return;
            gtagLoaded = true;

            // gtag.jsスクリプトを動的に作成・追加
            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=G-K4EYQCDK25';
            document.head.appendChild(script);

            // gtagの初期設定コマンドをキューにプッシュ
            gtag('js', new Date());
            gtag('config', 'G-K4EYQCDK25');
        };

        // ブラウザがアイドル状態になったときに読み込む
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(loadGtag);
        } else {
            // サポートしていない場合は、フォールバックとしてsetTimeoutを使用
            setTimeout(loadGtag, 6000);
        }

        // ユーザーの最初のインタラクションを監視して読み込む
        // ['scroll', 'mousemove', 'click', 'touchstart', 'keydown'].forEach(function(event) {
        //     window.addEventListener(event, loadGtag, {
        //         once: true
        //     });
        // });
    </script>
<?php
}




//GTAG遅延ロード e
///////////////////////////////////////////
///////////////////////////////////////////
///////////////////////////////////////////
///////////////////////////////////////////
///////////////////////////////////////////