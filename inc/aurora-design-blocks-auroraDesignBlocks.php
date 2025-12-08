<?php

if (! defined('ABSPATH')) exit;
/************************************************************/
/*cssのロード s*/
/************************************************************/




class AuroraDesignBlocksPreDetermineCssAssets
{
    private static $styles = [];

    private static $deferredStyles = [
        "aurora-design-style-aurora-design",
    ];

    public static function init()
    {
        global $post;
        // 以下、必要に応じて追加
        if (is_singular()) {
            self::$styles = array_merge(self::$styles, [
                'aurora-design-style-aurora-design' => 'css/build/aurora-design.css',
            ]);
        }

        if (is_archive() || is_search() || is_404()) {
            // 漏れているページ用の CSS をここで追加
            self::$styles = array_merge(self::$styles, [
                'aurora-design-style-aurora-design' => 'css/build/aurora-design.css',
            ]);
        }


        // スタイルリストを設定（追記可能）
        auroraDesignBlocksFrontendStyles::add_styles(self::$styles);

        // 遅延対象のスタイルを登録
        auroraDesignBlocksDeferCss::add_deferred_styles(self::$deferredStyles);
    }
}

// 初期化処理（ルートで実行）
add_action('wp', ['AuroraDesignBlocksPreDetermineCssAssets', 'init']);
/************************************************************/
/*cssのロード e*/
/************************************************************/































// OGR_s ////////////////////////////////////////////////////////////////////////////////



function AuroraDesignBlocks_add_ogp_meta_tags()
{
    if (!Aurora_Feature_Flags::ogp()) {
        return; // OGP 出力
    }
    if (is_singular() || is_front_page() || is_home()) { // トップページも対象
        global $post;


        if (is_front_page() || is_home()) {
            // トップページ用の値
            $title = esc_attr(get_bloginfo('name'));
            $excerpt = esc_attr(get_bloginfo('description'));
            $url = esc_url(home_url('/'));
        } else {

            $title = esc_attr(get_the_title($post));
            $excerpt = get_the_excerpt($post);
            $excerpt = esc_attr($excerpt);
            $url = esc_url(get_permalink($post));
        }
        $image = esc_url(AuroraDesignBlocksPostThumbnail::getUrl($post->ID, 'full'));
        $site_name = esc_attr(get_bloginfo('name'));
        $locale = esc_attr(get_locale());

        // 変数 $title, $excerpt, $url, $image, $site_name, $locale は、このコードの上で定義されているものとします。

        echo '' . "\n";
        // WordPressの標準的なサニタイズ・エスケープ関数を適用して出力します。
        // HTML属性に出力するため、esc_attr()を使用します。
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($excerpt) . '" />' . "\n";
        echo '<meta property="og:type" content="website" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n"; // URLはesc_url()が適切
        echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n"; // 画像URLもesc_url()が適切
        echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '" />' . "\n";
        echo '<meta property="og:locale" content="' . esc_attr($locale) . '" />' . "\n";
        echo '' . "\n";
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

        if ((!Aurora_Feature_Flags::toc()) || ($hide_toc == '1')) {
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
                $toc .= '<li class="toc-' . strtolower($heading_tag) . '">' . $indent . '<a href="#' . $id . '">' . wp_strip_all_tags($heading_text) . '</a></li>';


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
            <?php echo esc_html__('Hide TOC', 'aurora-design-blocks'); ?>
        </label>
<?php

    }

    /**
     * Save the TOC visibility meta box data.
     *
     * @param int $post_id Post ID.
     */
    public function save_toc_visibility_meta_box_data($post_id)
    {

        // Check if nonce is set.
        if (! isset($_POST['toc_visibility_nonce'])) {
            return;
        }

        if (
            ! wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['toc_visibility_nonce'])),
                'toc_visibility_nonce_action'
            )
        ) {
            return;
        }

        // Check if this is an autosave.
        if (wp_is_post_autosave($post_id)) {
            return;
        }

        // Check user permissions.
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        // Sanitize and save TOC visibility.
        $hide_toc = isset($_POST['hide_toc']) ? '1' : '0';
        $hide_toc = sanitize_text_field($hide_toc); // 明示的にサニタイズ

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

    /*
          * 指定投稿の表示用サムネイルHTMLを出力する。
          * @param int|null $post_id 投稿ID（省略時は現在の投稿）
          * @param string $size アイキャッチ画像のサイズ（デフォルト: 'medium'）
          * @param string $default_url デフォルト画像のURL（空なら /assets/default.webp）
          */
    public static function render($post_id = null, $size = 'medium', $default_url = '')
    {
        echo '<img src="' . esc_url(self::get_thumbnail_url($post_id, $size, $default_url)) . '" alt="">';

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


/********************************************************************/
/* google geminiでスラッグ対応 s	*/
/********************************************************************/

require_once plugin_dir_path(ADB_PLUGIN_FILE) . 'vendor/autoload.php';

use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;


/**
 * 投稿データ挿入前にスラッグを自動生成する。
 * Gemini AI Settings の設定に従って実行される。
 * * @param array $data 投稿データ配列。
 * @param array $postarr 未サニタイズの元のデータ配列。
 * @return array 変更された、または変更のない投稿データ配列。
 */
class Aurora_GeminiAI_Slug_Generator
{

    /**
     * クラスのシングルトンインスタンスを格納します。
     *
     * @var Aurora_GeminiAI_Slug_Generator|null
     */
    private static $instance = null;

    /**
     * シングルトンインスタンスを取得します。
     *
     * @return Aurora_GeminiAI_Slug_Generator
     */
    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * コンストラクタ。WordPressのフックを設定します。
     */
    private function __construct()
    {
        // WordPressのフックを登録
        add_filter('wp_insert_post_data', array($this, 'handle_post_data'), 10, 2);
    }

    /**
     * 投稿データ挿入前にスラッグを自動生成するメインメソッド。
     *
     * @param array $data 投稿データ配列。
     * @param array $postarr 未サニタイズの元のデータ配列。
     * @return array 変更された、または変更のない投稿データ配列。
     */
    public function handle_post_data($data, $postarr)
    {
        // 1. 基本チェック：タイトルが空、または自動保存中の場合はスキップ
        if (empty($data['post_title']) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
            return $data;
        }

        // 2. AI機能の有効化チェック
        // Aurora_GeminiAI_Settings クラスがロードされている必要があります
        if (! class_exists('Aurora_GeminiAI_Settings')) {
            return $data;
        }

        // AIスラッグ生成機能が無効な場合はスキップ
        if (! Aurora_GeminiAI_Settings::is_ai_slug_enabled()) {
            return $data;
        }

        // APIキーを取得。キーが空の場合はスキップ
        $gemini_api_key = Aurora_GeminiAI_Settings::get_api_key();
        if (empty($gemini_api_key)) {
            // APIキーが未設定の場合は、処理をスキップ
            return $data;
        }

        // 3. 実行条件のチェック
        // 既にスラッグが設定されている場合はスキップ
        if (! empty($data['post_name'])) {
            return $data;
        }

        // 既存投稿の更新（ID > 0）はスキップし、新規作成時の初回保存のみを対象とする
        if (isset($postarr['ID']) && $postarr['ID'] > 0) {
            return $data;
        }

        // ★★★ 実行条件を満たした場合、以降の処理へ進む ★★★

        // 4. キャッシュチェック
        $cache_key     = 'adb_slug_translate_' . md5($data['post_title']);
        $cached_slug = get_transient($cache_key);

        if ($cached_slug) {
            $data['post_name'] = $cached_slug;
            return $data;
        }

        // 5. API呼び出し
        try {
            // ★★★ 【注意】このClientクラスとTextPartクラスは、Google Gemini SDK
            // の名前空間に合わせて適切に定義・ロードされている必要があります。
            // 仮の定義として、元のコードに合わせています。

            // 正しいライブラリのuseステートメントを仮定
            $client = new Client($gemini_api_key);

            $prompt = "以下の日本語のタイトルを英語のスラッグに適した短いフレーズに翻訳し、その結果を半角スペースをハイフンに置き換え、すべて小文字にしたスラッグ形式で返してください。翻訳結果のみを返してください。\n\nタイトル: " . $data['post_title'];

            $response = $client->generativeModel('gemini-2.5-flash')
                ->generateContent(
                    new TextPart($prompt)
                );

            $translated_text = $response->text();
            // WordPressの組み込み関数でスラッグをサニタイズ
            $new_slug = sanitize_title($translated_text);

            // 6. キャッシュに保存し、スラッグを更新
            // 一時キャッシュを1時間（HOUR_IN_SECONDS * 1）設定
            set_transient($cache_key, $new_slug, HOUR_IN_SECONDS * 1);
            $data['post_name'] = $new_slug;
        } catch (\Exception $e) {
            // エラーが発生した場合はログに記録するのみで、処理は続行
            error_log('Gemini API error during slug generation: ' . $e->getMessage());
        }

        return $data;
    }
}


Aurora_GeminiAI_Slug_Generator::get_instance();


/********************************************************************/
/* google geminiでスラッグ対応 e	*/
/********************************************************************/
