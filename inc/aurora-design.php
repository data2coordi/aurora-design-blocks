<?php
function add_ogp_meta_tags()
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
add_action('wp_head', 'add_ogp_meta_tags');
