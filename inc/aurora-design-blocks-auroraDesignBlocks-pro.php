<?php

if (! defined('ABSPATH')) exit;
/************************************************************/
/*cssのロード s*/
/************************************************************/

class AuroraDesignBlocks_Utils
{

    /**
     * どれか1つでもアクティブなサイドバーがあるかを判定
     *
     * @return bool
     */
    public static function has_sidebar()
    {
        global $wp_registered_sidebars;

        if (empty($wp_registered_sidebars)) {
            return false;
        }

        foreach ($wp_registered_sidebars as $sidebar) {
            if (is_active_sidebar($sidebar['id'])) {
                return true;
            }
        }

        return false;
    }
}


class AuroraDesignBlocksPreDetermineCssAssets_pro
{
    private static $styles = [];

    private static $EditorStyles = [];

    private static $deferredStyles = [
        "aurora-design-blocks-style-module",
        "aurora-design-style-awesome",
    ];

    public static function init()
    {
        global $post;
        // 以下、必要に応じて追加
        if (is_singular() || AuroraDesignBlocks_Utils::has_sidebar()) {
            self::$styles = array_merge(self::$styles, [
                'aurora-design-blocks-style-module' => 'css/build/module.css',
            ]);
            $has_fa = false;

            // 本文チェック
            if (isset($post) && strpos($post->post_content, '[fontawesome') !== false) {
                $has_fa = true;
            }

            // サイドバーチェック
            if (!$has_fa && self::sidebar_has_fontawesome()) {
                $has_fa = true;
            }
            if ($has_fa) {
                self::$styles = array_merge(self::$styles, [
                    'aurora-design-style-awesome' => 'css/build/awesome-all.css',
                ]);
            }
        }

        if (is_archive() || is_search() || is_404()) {
            // 漏れているページ用の CSS をここで追加
            self::$styles = array_merge(self::$styles, [
                'aurora-design-blocks-style-module' => 'css/build/module.css',
            ]);
        }


        // スタイルリストを設定（追記可能）
        auroraDesignBlocksFrontendStyles::add_styles(self::$styles);

        // 遅延対象のスタイルを登録
        auroraDesignBlocksDeferCss::add_deferred_styles(self::$deferredStyles);
    }

    public static function init_forEditor()
    {
        self::$EditorStyles = [
            'aurora-design-style-awesome' => 'css/build/awesome-all.css',
        ];
        AuroraDesignBlocksEditorStyles::add_styles(self::$EditorStyles);
    }

    private static function sidebar_has_fontawesome()
    {
        $sidebars = wp_get_sidebars_widgets();

        if (empty($sidebars)) {
            return false;
        }

        // 旧テキストウィジェットの取得
        $text_widgets = get_option('widget_text', []);
        // ブロックウィジェットの取得（WordPress 5.8+）
        $block_widgets = get_option('widget_block', []);

        foreach ($sidebars as $sidebar => $widgets) {

            if (empty($widgets)) {
                continue;
            }

            foreach ($widgets as $widget_id) {

                /*** ① 旧テキストウィジェット(text-xxx) ***/
                if (strpos($widget_id, 'text-') === 0) {
                    $id = str_replace('text-', '', $widget_id);
                    if (isset($text_widgets[$id]['text'])) {
                        if (strpos($text_widgets[$id]['text'], '[fontawesome') !== false) {
                            return true;
                        }
                    }
                }

                /*** ② ブロックウィジェット(block-xxx) ***/
                if (strpos($widget_id, 'block-') === 0) {
                    $id = str_replace('block-', '', $widget_id);
                    if (isset($block_widgets[$id]['content'])) {
                        $content = $block_widgets[$id]['content'];

                        // ブロック内の H2, P, shortcode block 等を横断的に検査
                        if (strpos($content, '[fontawesome') !== false) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}

// 初期化処理（ルートで実行）
add_action('wp', ['AuroraDesignBlocksPreDetermineCssAssets_pro', 'init']);
add_action('init', [
    'AuroraDesignBlocksPreDetermineCssAssets_pro',
    'init_forEditor',
]);
/************************************************************/
/*cssのロード e*/
/************************************************************/
