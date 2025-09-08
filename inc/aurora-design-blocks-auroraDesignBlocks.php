<?php

/************************************************************/
/*cssのロード s*/
/************************************************************/




class AuroraDesignBlocksPreDetermineCssAssets
{
    private static $styles = [];

    private static $EditorStyles = [];

    private static $deferredStyles = [
        "aurora-design-blocks-style-block-module",
        "aurora-design-style-aurora-design",
        "aurora-design-style-awesome",
    ];

    public static function init()
    {
        global $post;
        // 以下、必要に応じて追加
        if (is_singular()) {
            self::$styles = array_merge(self::$styles, [
                'aurora-design-blocks-style-block-module' => 'css/build/block-module.css',
                'aurora-design-style-aurora-design' => 'css/build/aurora-design.css',
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
                'aurora-design-blocks-style-block-module' => 'css/build/block-module.css',
                'aurora-design-style-aurora-design' => 'css/build/aurora-design.css',
                'aurora-design-style-awesome' => 'css/build/awesome-all.css',
            ]);
        }

        if (is_home()) {
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
add_action('wp', ['AuroraDesignBlocksPreDetermineCssAssets', 'init']);
add_action('init', [
    'AuroraDesignBlocksPreDetermineCssAssets',
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





















// OGR_s ////////////////////////////////////////////////////////////////////////////////



function AuroraDesignBlocks_add_ogp_meta_tags()
{
    if (is_singular()) { // 投稿・固定ページなどの単一ページでのみ出力
        global $post;

        $title = esc_attr(get_the_title($post));
        $excerpt = get_the_excerpt($post);
        if (empty($excerpt)) {
            $content = get_the_content(null, false, $post);
            $excerpt = wp_trim_words(strip_tags($content), 25, '...');
        }
        $excerpt = esc_attr($excerpt);
        $url = esc_url(get_permalink($post));
        $image = has_post_thumbnail($post) ? esc_url(get_the_post_thumbnail_url($post, 'full')) : '';
        $site_name = esc_attr(get_bloginfo('name'));
        $locale = esc_attr(get_locale());

        echo <<<HTML
    <!-- OGP Meta Tags s -->
    <meta property="og:title" content="{$title}" />
    <meta property="og:description" content="{$excerpt}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{$url}" />
    <meta property="og:image" content="{$image}" />
    <meta property="og:site_name" content="{$site_name}" />
    <meta property="og:locale" content="{$locale}" />
    <!-- OGP Meta Tags e -->
    HTML;
    }
}
add_action('wp_head', 'AuroraDesignBlocks_add_ogp_meta_tags');
// OGR_e ////////////////////////////////////////////////////////////////////////////////


// 目次_s ////////////////////////////////////////////////////////////////////////////////
// 目次を生成するクラスを定義

class AuroraDesignBlocksTableOfContents
{

    // コンストラクタ
    public function __construct()
    {
        add_filter('the_content', array($this, 'add_toc_to_content'));
        add_action('add_meta_boxes', array($this, 'add_toc_visibility_meta_box'));
        add_action('save_post', array($this, 'save_toc_visibility_meta_box_data'));
    }

    // 投稿コンテンツに目次を追加するメソッド
    public function add_toc_to_content($content)
    {



        $hide_toc = get_post_meta(get_the_ID(), 'hide_toc', true);

        if ($hide_toc == '1') {
            return $content;
        }



        // H1, H2, H3タグを抽出
        preg_match_all('/<(h[1-3])([^>]*)>(.*?)<\/\1>/', $content, $matches, PREG_SET_ORDER);

        if (!empty($matches)) {
            // 目次を生成
            $toc = '<div class="post-toc"><b>' . esc_html(__('Index', 'aurora-design-blocks')) . '</b><ul>';
            foreach ($matches as $match) {
                $heading_tag = $match[1]; // h1, h2, h3
                $heading_attributes = $match[2]; // クラスやIDなどの属性
                $heading_text = $match[3]; // 見出しのテキスト
                // HタグにIDを追加してクラスを維持
                $id = sanitize_title_with_dashes($heading_text);


                // 目次を作成
                // インデント調整（追加部分）
                $indent = '';
                if ($heading_tag === 'h2') {
                    $indent = '&nbsp;&nbsp;'; // H2ならインデント1つ
                } elseif ($heading_tag === 'h3') {
                    $indent = '&nbsp;&nbsp;&nbsp;&nbsp;'; // H3ならインデント2つ
                }

                // 目次を作成
                $toc .= '<li class="toc-' . strtolower($heading_tag) . '">' . $indent . '<a href="#' . $id . '">' . strip_tags($heading_text) . '</a></li>';


                $content = str_replace(
                    $match[0],
                    '<' . $heading_tag . $heading_attributes . ' id="' . $id . '">' . $heading_text . '</' . $heading_tag . '>',
                    $content
                );
            }
            $toc .= '</ul></div>';

            // 目次をコンテンツの最初に追加
            $content = $toc . $content;
        }

        return $content;
    }


    public function add_toc_visibility_meta_box()
    {
        $screens = ['post', 'page'];
        add_meta_box(
            'toc_visibility_meta_box', // ID
            __('TOC Visibility', 'aurora-design-blocks'), // タイトル
            array($this, 'render_toc_visibility_meta_box'), // コールバック関数
            $screens, // 投稿タイプ
            'side', // コンテキスト
            'default' // 優先度
        );
    }

    public  function render_toc_visibility_meta_box($post)
    {
        $value = get_post_meta($post->ID, 'hide_toc', true);
        wp_nonce_field('toc_visibility_nonce_action', 'toc_visibility_nonce');
?>
        <label for="hide_toc">
            <input type="checkbox" name="hide_toc" id="hide_toc" value="1" <?php checked($value, '1'); ?> />
            <?php echo __('Hide TOC', 'aurora-design-blocks'); ?>
        </label>
<?php

    }

    public  function save_toc_visibility_meta_box_data($post_id)
    {
        if (!isset($_POST['toc_visibility_nonce'])) {
            return;
        }
        if (!wp_verify_nonce(wp_unslash($_POST['toc_visibility_nonce']), 'toc_visibility_nonce_action')) {
            return;
        }
        if (wp_is_post_autosave($post_id)) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $hide_toc = isset($_POST['hide_toc']) ? '1' : '0';
        update_post_meta($post_id, 'hide_toc', $hide_toc);
    }
}

// インスタンスを作成して目次生成を初期化
new AuroraDesignBlocksTableOfContents();

// 目次_e ////////////////////////////////////////////////////////////////////////////////

/********************************************************************/
/* サムネイル取得(存在しなければ、本文の画像、デフォルト画像を取得) s	*/
/********************************************************************/

class AuroraDesignBlocksPostThumbnail
{


    private static function get_thumbnail_url($post_id = null, $size = 'medium', $default_url = '')
    {


        if (is_null($post_id)) {
            $post_id = get_the_ID();
        }

        // アイキャッチ画像がある場合
        if (has_post_thumbnail($post_id)) {
            $thumbnail_url = get_the_post_thumbnail_url($post_id, $size);
            return esc_url($thumbnail_url);
        };

        // 本文から最初の画像を抽出
        $content = get_post_field('post_content', $post_id);
        preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $image);

        if (!empty($image['src'])) {
            return esc_url($image['src']);
        }

        // デフォルト画像（未指定時は /assets/default.webp）
        if (empty($default_url)) {
            $default_url = get_template_directory_uri() . '/assets/default.webp';
            return esc_url($default_url);
        }
    }

    /**
     * 指定投稿の表示用サムネイルHTMLを出力する。
     * @param int|null $post_id 投稿ID（省略時は現在の投稿）
     * @param string $size アイキャッチ画像のサイズ（デフォルト: 'medium'）
     * @param string $default_url デフォルト画像のURL（空なら /assets/default.webp）
     */
    public static function render($post_id = null, $size = 'medium', $default_url = '')
    {
        echo '<img src="' . self::get_thumbnail_url($post_id, $size, $default_url) . '" alt="">';

        return;
    }

    public static function getUrl($post_id = null, $size = 'medium', $default_url = '')
    {
        return self::get_thumbnail_url($post_id, $size, $default_url);
    }
}
/********************************************************************/
/* サムネイル取得(存在しなければ、本文の画像、デフォルト画像を取得) e	*/
/********************************************************************/
