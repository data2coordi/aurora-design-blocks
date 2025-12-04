<?php

/**
 * Plugin Name: Aurora Design Blocks Related Posts
 * Description: 本文中のリンク構造に基づき、最も関連性の高い記事を自動表示するブロックを実装します。
 * Version: 1.0
 * Author: Your Name
 */

// プラグインファイルへの絶対パス（register_activation_hook用）
// メインファイルがブロックフォルダ内にあっても問題なく機能します

// グローバル変数としてDBテーブル名を定義
global $wpdb;
$adb_links_table = $wpdb->prefix . 'adb_links';


// =================================================================
// 1. データベース管理とリンク解析 (変更なし)
// =================================================================

/**
 * 相互参照型リンクテーブルを作成する関数
 */
function adb_create_links_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'adb_links';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        source_post_id BIGINT(20) UNSIGNED NOT NULL,
        target_post_id BIGINT(20) UNSIGNED NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY (source_post_id, target_post_id),
        KEY idx_target_id (target_post_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(ADB_PLUGIN_FILE, 'adb_create_links_table');


/**
 * 【補助関数】本文から内部リンクの投稿IDを抽出する
 */
function adb_extract_internal_links($content)
{
    $links = [];
    $home_url = get_home_url();

    if (preg_match_all('/<a\s+(?:[^>]*?\s+)?href=["\']([^"\'#]+)["\']/i', $content, $matches)) {
        foreach ($matches[1] as $url) {
            if (strpos($url, $home_url) === 0) {
                $post_id = url_to_postid($url);
                if ($post_id) {
                    $links[] = $post_id;
                }
            }
        }
    }
    return array_unique($links);
}


/**
 * 投稿保存時に本文を解析し、リンクテーブルを更新する
 */
function adb_update_related_links_minimal($post_id)
{
    global $wpdb, $adb_links_table;

    // ガード句
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    $post_type = get_post_type($post_id);
    if (!in_array($post_type, array('post', 'page'))) return;

    $post_content = get_post_field('post_content', $post_id);

    // 既存リンクを削除
    $wpdb->delete($adb_links_table, array('source_post_id' => $post_id), array('%d'));

    // 新しいリンクを抽出
    $new_link_ids = adb_extract_internal_links($post_content);

    if (empty($new_link_ids)) {
        return;
    }

    // 新しいリンクを挿入
    $insert_queries = array();
    $current_time = current_time('mysql');
    foreach ($new_link_ids as $target_id) {
        if ($target_id != $post_id) {
            $insert_queries[] = $wpdb->prepare(
                "(%d, %d, %s)",
                $post_id,
                $target_id,
                $current_time
            );
        }
    }

    if (!empty($insert_queries)) {
        $sql = "INSERT INTO $adb_links_table (source_post_id, target_post_id, updated_at) VALUES ";
        $sql .= implode(', ', $insert_queries);
        $wpdb->query($sql);
    }
}
add_action('save_post', 'adb_update_related_links_minimal');


// =================================================================
// 2. コアロジック：関連記事の取得とスコアリング (変更なし)
// =================================================================

/**
 * 関連記事のリスト（被リンク元）を取得する関数 (スコアを投稿オブジェクトに付加)
 */
function adb_get_reciprocal_related_posts($current_post_id, $limit = 5)
{
    global $wpdb, $adb_links_table;

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT  
            source_post_id AS related_id, 
            COUNT(source_post_id) AS score 
           FROM 
             $adb_links_table 
           WHERE 
             target_post_id = %d 
           GROUP BY 
             source_post_id 
           ORDER BY 
             score DESC, updated_at DESC 
           LIMIT %d",
        $current_post_id,
        $limit
    ), OBJECT);

    if (empty($results)) {
        return array();
    }

    $related_posts_with_score = [];
    foreach ($results as $r) {
        $post = get_post($r->related_id);
        if ($post) {
            $post->score = $r->score;
            $related_posts_with_score[] = $post;
        }
    }

    return $related_posts_with_score;
}


// =================================================================
// 3. Gutenbergブロック連携
// =================================================================

// ------------------------------------------------------------------
// REST API エンドポイントの登録とコールバック (変更なし)
// ------------------------------------------------------------------

add_action('rest_api_init', 'adb_register_related_posts_api');
function adb_register_related_posts_api()
{
    register_rest_route('aurora-design-blocks/v1', '/related-posts', array(
        'methods' => 'GET',
        'callback' => 'adb_api_get_related_posts',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));
}

/**
 * REST APIコールバック関数 (JSのapiFetchに対応)
 */
function adb_api_get_related_posts($request)
{
    $post_id = intval($request->get_param('post_id'));
    $limit = intval($request->get_param('limit'));
    $show_excerpt = filter_var($request->get_param('excerpt'), FILTER_VALIDATE_BOOLEAN);

    if (!$post_id || $limit <= 0) {
        return new WP_REST_Response([], 200);
    }

    $related_posts = adb_get_reciprocal_related_posts($post_id, $limit);

    $formatted_posts = [];
    foreach ($related_posts as $post) {
        $excerpt = $show_excerpt ? get_the_excerpt($post) : '';

        $formatted_posts[] = [
            'id' => $post->ID,
            'title' => get_the_title($post),
            'link' => get_permalink($post),
            'score' => isset($post->score) ? $post->score : null,
            'excerpt' => $excerpt,
        ];
    }

    return new WP_REST_Response($formatted_posts, 200);
}

// ------------------------------------------------------------------
// フロントエンドのレンダリングコールバック (変更なし)
// ------------------------------------------------------------------

/**
 * 記事表示時にHTMLを生成する関数 (本番環境での出力)
 */
function adb_render_related_posts_block_html($attributes, $content)
{
    $limit = isset($attributes['limit']) ? intval($attributes['limit']) : 5;
    $style = isset($attributes['styleType']) ? esc_attr($attributes['styleType']) : 'list';
    $show_excerpt = filter_var(isset($attributes['showExcerpt']) ? $attributes['showExcerpt'] : false, FILTER_VALIDATE_BOOLEAN);

    $current_post_id = get_the_ID();
    if (!$current_post_id || !in_the_loop()) {
        return '';
    }

    $related_posts = adb_get_reciprocal_related_posts($current_post_id, $limit);

    if (empty($related_posts)) {
        return '';
    }

    $html = sprintf(
        '<div class="wp-block-aurora-design-blocks-related-posts adb-style-%s">',
        $style
    );

    $html .= '<h2>関連記事</h2>';
    $html .= '<ul>';

    foreach ($related_posts as $post) {
        $title = get_the_title($post);
        $link = get_permalink($post);

        $html .= '<li class="related-post-item">';
        $html .= sprintf('<a href="%s">%s</a>', esc_url($link), esc_html($title));

        if ($show_excerpt) {
            $excerpt = get_the_excerpt($post);
            $html .= sprintf('<p class="adb-excerpt">%s</p>', esc_html($excerpt));
        }

        $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</div>';

    return $html;
}
