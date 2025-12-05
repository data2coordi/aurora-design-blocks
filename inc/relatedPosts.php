<?php
// ファイル名: class-adbl-links-db-manager.php

class AuroraDesignBlocks_RelatedPosts_DBManager
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

class AuroraDesignBlocks_RelatedPosts_LinkAnalyzer
{
    private $db_manager;

    public function __construct(AuroraDesignBlocks_RelatedPosts_DBManager $db_manager)
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

class AuroraDesignBlocks_RelatedPosts_Query
{
    private $db_manager;

    public function __construct(AuroraDesignBlocks_RelatedPosts_DBManager $db_manager)
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



// ファイル名: class-adbl-block-frontend.php

class AuroraDesignBlocks_RelatedPosts_BlockFrontend
{
    private $query_handler;

    public function __construct(AuroraDesignBlocks_RelatedPosts_Query $query_handler)
    {
        $this->query_handler = $query_handler;
    }




// class-adbl-block-frontend.php の AuroraDesignBlocks_RelatedPosts_BlockFrontend クラス内

    /**
     * ブロックのサーバーサイドレンダリングコールバック
     */
    // class-adbl-block-frontend.php の AuroraDesignBlocks_RelatedPosts_BlockFrontend クラス内

    /**
     * ブロックのサーバーサイドレンダリングコールバック
     */
    // class-adbl-block-frontend.php の AuroraDesignBlocks_RelatedPosts_BlockFrontend クラス内

    /**
     * ブロックのサーバーサイドレンダリングコールバック
     */
    public function render_related_posts_block_html($attributes, $content)
    {
        $limit = isset($attributes['limit']) ? intval($attributes['limit']) : 5;
        $style = isset($attributes['styleType']) ? esc_attr($attributes['styleType']) : 'list';
        $show_excerpt = filter_var(isset($attributes['showExcerpt']) ? $attributes['showExcerpt'] : false, FILTER_VALIDATE_BOOLEAN);

        // SSRリクエストであるかを確認（エディターでのプレビュー時）
        $is_ssr_request = defined('REST_REQUEST') && REST_REQUEST;

        // IDの取得:
        // フロントエンドでは get_queried_object_id() を使用（ループ内外で信頼性が高い）
        // エディターでは get_the_ID() を使用（エディター独自のコンテキストに対応）
        $id_for_query = $is_ssr_request ? get_the_ID() : get_queried_object_id();
        $current_post_id = intval($id_for_query);


        // 1. フロントエンドでの表示条件チェック（SSRではない場合のみ）
        if (!$is_ssr_request) {
            // 単一ページではない、または有効な投稿IDがない場合はレンダリングしない
            // (get_queried_object_id()を使用しているため、サイドバーでもIDを取得可能)
            if (!is_singular() || $current_post_id === 0) {
                return '';
            }
        }

        // --- 2. エディタープレビュー時のガイドメッセージ (IDがない場合) ---
        // ここは $is_ssr_request=true の時、かつ $current_post_id=0 の場合に実行される
        if (!$current_post_id) {
            $html = sprintf(
                '<div class="wp-block-aurora-design-blocks-related-posts adb-style-%s adb-editor-guide" style="padding: 15px; border: 2px dashed #007cba; text-align: center;">',
                $style
            );
            $html .= esc_html__('[相互参照型関連記事ブロック] プレビュー', 'aurora-design-blocks');
            $html .= '<p style="margin-top: 5px; font-size: 13px;">' . esc_html__('（現在の投稿IDが取得できないため、ライブデータは表示されません。記事を保存してください。）', 'aurora-design-blocks') . '</p>';
            $html .= '</div>';
            return $html;
        }

        // --- 3. データ取得とHTML生成 (有効なIDがある場合) ---
        $related_posts = $this->query_handler->get_reciprocal_related_posts($current_post_id, $limit);

        // 関連記事が見つからなかった場合
        if (empty($related_posts)) {
            // エディターの場合（$is_ssr_request=true）は「データなし」ガイドを返す
            if ($is_ssr_request) {
                $html = sprintf(
                    '<div class="wp-block-aurora-design-blocks-related-posts adb-style-%s adb-editor-no-data" style="padding: 15px; border: 2px dashed #ffba00; text-align: center;">',
                    $style
                );
                $html .= esc_html__('[相互参照型関連記事ブロック] データなし', 'aurora-design-blocks');
                $html .= '<p style="margin-top: 5px; font-size: 13px;">' . esc_html__('データベースに被リンクデータが見つかりませんでした。', 'aurora-design-blocks') . '</p>';
                $html .= '</div>';
                return $html;
            }
            return ''; // フロントエンドでは空文字
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


class AuroraDesignBlocks_RelatedPosts_Plugin
{
    private $db_manager;
    private $link_analyzer;
    private $query_handler;
    private $frontend;

    public function __construct()
    {
        global $wpdb;

        // 依存関係の注入と初期化
        $this->db_manager    = new AuroraDesignBlocks_RelatedPosts_DBManager($wpdb);
        $this->query_handler = new AuroraDesignBlocks_RelatedPosts_Query($this->db_manager);
        $this->link_analyzer = new AuroraDesignBlocks_RelatedPosts_LinkAnalyzer($this->db_manager);
        $this->frontend      = new AuroraDesignBlocks_RelatedPosts_BlockFrontend($this->query_handler);

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
new AuroraDesignBlocks_RelatedPosts_Plugin();
