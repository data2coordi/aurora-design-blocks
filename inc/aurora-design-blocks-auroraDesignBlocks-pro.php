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
            if (isset($post) && strpos($post->post_content, '[fontawesome') !== false) {
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
