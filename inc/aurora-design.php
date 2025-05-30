<?php
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
            $toc = '<div class="post-toc"><B>Index</B><ul>';
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
            __('TOC Visibility', 'integlight'), // タイトル
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
            <?php echo __('Hide TOC', 'integlight'); ?>
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
