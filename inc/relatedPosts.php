<?php
// ファイル名: class-adbl-links-db-manager.php

class ADBL_LinksDBManager
{
    private $adb_links_table;
    private $wpdb;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
        $this->adb_links_table = $this->wpdb->prefix . 'adb_links';
    }

    /**
     * 相互参照型リンクテーブルを作成する
     */
    public function create_links_table()
    {
        $table_name = $this->adb_links_table;
        $charset_collate = $this->wpdb->get_charset_collate();

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

    /**
     * 特定の投稿IDに関連する既存リンクを全て削除する（DB登録操作）
     * @param int $post_id 削除対象のリンク元ID
     */
    public function delete_links_by_source_id($post_id)
    {
        return $this->wpdb->delete(
            $this->adb_links_table,
            array('source_post_id' => $post_id),
            array('%d')
        );
    }

    /**
     * 新しいリンクを一括挿入する（DB登録操作）
     * @param int $source_id リンク元の投稿ID
     * @param array $target_ids リンク先の投稿IDの配列
     */
    public function bulk_insert_links($source_id, array $target_ids)
    {
        if (empty($target_ids)) return 0;

        $insert_queries = array();
        $current_time = current_time('mysql');

        foreach ($target_ids as $target_id) {
            if ($target_id != $source_id) {
                $insert_queries[] = $this->wpdb->prepare(
                    "(%d, %d, %s)",
                    $source_id,
                    $target_id,
                    $current_time
                );
            }
        }

        if (!empty($insert_queries)) {
            $sql = "INSERT INTO {$this->adb_links_table} (source_post_id, target_post_id, updated_at) VALUES ";
            $sql .= implode(', ', $insert_queries);
            return $this->wpdb->query($sql);
        }
        return 0;
    }

    /**
     * 関連記事のリスト（被リンク元）を取得する（DB検索操作）
     * @param int $target_post_id 現在の投稿ID（被リンク先）
     * @param int $limit 取得制限数
     * @return array 関連投稿のIDとスコアを含むオブジェクトの配列
     */
    public function get_related_post_ids_and_scores($target_post_id, $limit)
    {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT
                source_post_id AS related_id,
                COUNT(source_post_id) AS score
            FROM
                {$this->adb_links_table}
            WHERE
                target_post_id = %d
            GROUP BY
                source_post_id
            ORDER BY
                score DESC, updated_at DESC
            LIMIT %d",
            $target_post_id,
            $limit
        ), OBJECT);
    }
}


// ファイル名: class-adbl-link-analyzer.php

class ADBL_LinkAnalyzer
{
    private $db_manager;

    public function __construct(ADBL_LinksDBManager $db_manager)
    {
        $this->db_manager = $db_manager;
    }

    /**
     * 投稿保存アクションにフックし、リンクテーブルを更新する
     * @param int $post_id
     */
    public function update_related_links_on_save($post_id)
    {
        // ガード句
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        $post_type = get_post_type($post_id);
        if (!in_array($post_type, array('post', 'page'))) return;

        $post_content = get_post_field('post_content', $post_id);

        // 1. 既存リンクを削除
        $this->db_manager->delete_links_by_source_id($post_id);

        // 2. 新しいリンクを抽出
        $new_link_ids = $this->extract_internal_links($post_content);

        // 3. 新しいリンクを挿入
        if (!empty($new_link_ids)) {
            $this->db_manager->bulk_insert_links($post_id, $new_link_ids);
        }
    }

    /**
     * 【補助関数】本文から内部リンクの投稿IDを抽出する
     * @param string $content 投稿本文
     * @return array 内部リンク先の投稿IDの配列
     */
    private function extract_internal_links($content)
    {
        $links = [];
        $home_url = get_home_url();

        if (preg_match_all('/<a\s+(?:[^>]*?\s+)?href=["\']([^"\'#]+)["\']/i', $content, $matches)) {
            foreach ($matches[1] as $url) {
                // ホームURLで始まる内部リンクかチェック
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
}
// ファイル名: class-adbl-related-posts-query.php

class ADBL_RelatedPostsQuery
{
    private $db_manager;

    public function __construct(ADBL_LinksDBManager $db_manager)
    {
        $this->db_manager = $db_manager;
    }

    /**
     * 関連記事のリスト（被リンク元）を取得する (スコアを投稿オブジェクトに付加)
     * @param int $current_post_id 現在の投稿ID
     * @param int $limit 取得制限数
     * @return array スコアが付加された投稿オブジェクトの配列
     */
    public function get_reciprocal_related_posts($current_post_id, $limit = 5)
    {
        $results = $this->db_manager->get_related_post_ids_and_scores($current_post_id, $limit);

        if (empty($results)) {
            return array();
        }

        $related_posts_with_score = [];
        foreach ($results as $r) {
            $post = get_post($r->related_id);
            if ($post) {
                // スコアを投稿オブジェクトに動的に付加
                $post->score = $r->score;
                $related_posts_with_score[] = $post;
            }
        }

        return $related_posts_with_score;
    }
}


// ファイル名: class-adbl-rest-api-endpoints.php

class ADBL_RestApiEndpoints
{
    private $query_handler;

    public function __construct(ADBL_RelatedPostsQuery $query_handler)
    {
        $this->query_handler = $query_handler;
    }

    /**
     * REST API エンドポイントの登録
     */
    public function register_api_endpoint()
    {
        register_rest_route('aurora-design-blocks/v1', '/related-posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_related_posts_callback'),
            'permission_callback' => function () {
                // 編集権限を持つユーザーのみアクセス可能
                return current_user_can('edit_posts');
            }
        ));
    }

    /**
     * REST APIコールバック関数 (JSのapiFetchに対応)
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_related_posts_callback($request)
    {
        $post_id = intval($request->get_param('post_id'));
        $limit = intval($request->get_param('limit'));
        // $show_excerptはフロントエンドでのみ必要だが、エディタでのプレビューにも利用可能
        $show_excerpt = filter_var($request->get_param('excerpt'), FILTER_VALIDATE_BOOLEAN);

        if (!$post_id || $limit <= 0) {
            return new WP_REST_Response([], 200);
        }

        $related_posts = $this->query_handler->get_reciprocal_related_posts($post_id, $limit);

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
}


// ファイル名: class-adbl-block-frontend.php

class ADBL_BlockFrontend
{
    private $query_handler;

    public function __construct(ADBL_RelatedPostsQuery $query_handler)
    {
        $this->query_handler = $query_handler;
    }

    /**
     * ブロックのサーバーサイドレンダリングコールバック
     * @param array $attributes ブロックの属性
     * @param string $content 子要素のHTML (このブロックでは通常空)
     * @return string レンダリングされたHTML
     */
    public function render_related_posts_block_html($attributes, $content)
    {
        $limit = isset($attributes['limit']) ? intval($attributes['limit']) : 5;
        $style = isset($attributes['styleType']) ? esc_attr($attributes['styleType']) : 'list';
        $show_excerpt = filter_var(isset($attributes['showExcerpt']) ? $attributes['showExcerpt'] : false, FILTER_VALIDATE_BOOLEAN);

        $current_post_id = get_the_ID();
        // 投稿表示ページ以外、またはメインループ外ではレンダリングしない
        if (!$current_post_id || !in_the_loop()) {
            return '';
        }

        $related_posts = $this->query_handler->get_reciprocal_related_posts($current_post_id, $limit);

        if (empty($related_posts)) {
            return '';
        }

        // HTML生成ロジック
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
}


/**
 * Plugin Name: Aurora Design Blocks Related Posts
 * Description: 本文中のリンク構造に基づき、最も関連性の高い記事を自動表示するブロックを実装します。
 * Version: 1.0
 * Author: Your Name
 */

// グローバル変数としてDBテーブル名を定義 (メインファイルでの定義は必須ではありませんが、元のコードに倣う)
global $wpdb;
// $adb_links_table = $wpdb->prefix . 'adb_links'; // テーブル名はクラス内で管理

// プラグインファイルへの絶対パスの定数定義
if (!defined('ADB_PLUGIN_FILE')) {
    define('ADB_PLUGIN_FILE', __FILE__);
}

// 依存するクラスファイルを読み込む（実際にはオートロードが理想）
// require_once 'class-adbl-links-db-manager.php';
// require_once 'class-adbl-link-analyzer.php';
// require_once 'class-adbl-related-posts-query.php';
// require_once 'class-adbl-rest-api-endpoints.php';
// require_once 'class-adbl-block-frontend.php';


class ADBL_Plugin
{
    private $db_manager;
    private $link_analyzer;
    private $query_handler;
    private $rest_api;
    private $frontend;

    public function __construct()
    {
        global $wpdb;

        // 依存関係の注入と初期化
        $this->db_manager    = new ADBL_LinksDBManager($wpdb);
        $this->query_handler = new ADBL_RelatedPostsQuery($this->db_manager);
        $this->link_analyzer = new ADBL_LinkAnalyzer($this->db_manager);
        $this->rest_api      = new ADBL_RestApiEndpoints($this->query_handler);
        $this->frontend      = new ADBL_BlockFrontend($this->query_handler);

        $this->setup_hooks();
    }

    /**
     * WordPressのフックへの登録
     */
    private function setup_hooks()
    {
        // 1. アクティベーションフック (DBテーブル作成)
        register_activation_hook(ADB_PLUGIN_FILE, array($this->db_manager, 'create_links_table'));

        // 2. リンクテーブル更新（投稿保存時）
        add_action('save_post', array($this->link_analyzer, 'update_related_links_on_save'));

        // 3. REST API エンドポイントの登録（GUIエディタ用データ取得）
        add_action('rest_api_init', array($this->rest_api, 'register_api_endpoint'));

        // 4. Gutenbergブロックの登録とフロントエンドレンダリング
        // 実際のブロック登録コードが必要です。ここではレンダリング部分のみをフック。
        add_action('init', array($this, 'register_block'));
    }

    /**
     * Gutenbergブロックの登録（スタブ）
     */
    public function register_block()
    {
        // 実際のブロック登録処理。ここでは、サーバーサイドレンダリングのコールバックを設定します。
        // register_block_type('aurora-design-blocks/related-posts', array(
        //     'render_callback' => array($this->frontend, 'render_related_posts_block_html'),
        //     // ...その他の設定
        // ));

        // 仮のブロック登録フック（実際のJS/JSONファイルが必要です）
        // 簡略化のため、ここではレンダリングコールバックの設定を直接行います。
        if (function_exists('register_block_type')) {
            register_block_type(AURORA_DESIGN_BLOCKS_PATH . '/blocks/related-posts', array(
                'attributes' => array(
                    'limit' => array('type' => 'number', 'default' => 5),
                    'styleType' => array('type' => 'string', 'default' => 'list'),
                    'showExcerpt' => array('type' => 'boolean', 'default' => false),
                ),
                'render_callback' => array($this->frontend, 'render_related_posts_block_html'),
            ));
        }
    }
}

// プラグインの実行
new ADBL_Plugin();
