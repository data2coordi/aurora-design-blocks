<?php

/************************************************************/
/*cssのロード s*/
/************************************************************/




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
        if (is_singular()) {
            self::$styles = array_merge(self::$styles, [
                'aurora-design-blocks-style-module' => 'css/build/module.css',
            ]);
            if (strpos($post->post_content, '[fontawesome') !== false) {
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



// 国際化対応_s ////////////////////////////////////////////////////////////////////////////////

function aurora_design_blocks_load_textdomain()
{
    // AURORA_DESIGN_BLOCKS_PATH はプラグインのルートディレクトリへの絶対パスです。
    // load_plugin_textdomain の第3引数は、WP_PLUGIN_DIR からの相対パス、
    // またはプラグインのルートディレクトリからの相対パスを期待します。
    // 例: 'aurora-design-blocks/languages'
    $plugin_folder_name = basename(rtrim(AURORA_DESIGN_BLOCKS_PATH, '/')); // 'aurora-design-blocks' を取得
    $languages_relative_path = $plugin_folder_name . '/languages'; // 'aurora-design-blocks/languages' を作成
    $loaded = load_plugin_textdomain(
        'aurora-design-blocks', // テキストドメイン
        false,
        $languages_relative_path
    );
}
add_action('plugins_loaded', 'aurora_design_blocks_load_textdomain');


// 国際化対応_e ////////////////////////////////////////////////////////////////////////////////
