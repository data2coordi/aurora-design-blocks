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
            PRIMARY KEY (source_post_id,target_post_id),
            KEY idx_target_id (target_post_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function truncate()
    {
        return $this->wpdb->query("TRUNCATE TABLE {$this->adb_links_table}");
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
                COUNT(source_post_id) AS score,
                max(updated_at) As updated_at_max
            FROM
                {$this->adb_links_table}
            WHERE
                target_post_id = %d
            GROUP BY
                source_post_id
            ORDER BY
                score DESC, updated_at_max DESC
            LIMIT %d",
            $target_post_id,
            $limit
        ), OBJECT);
    }

    public function get_table_name()
    {
        return $this->adb_links_table;
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

    public function extract_internal_links($content)
    {
        $links = [];
        $home_url = home_url();
        $home_host = wp_parse_url($home_url, PHP_URL_HOST);

        // HTML 読み込み（エラー抑制）
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//a[@href]');

        foreach ($nodes as $node) {
            $url = $node->getAttribute('href');
            if (!$url) continue;

            // ▼ 絶対URL → ホストチェック
            $parsed = wp_parse_url($url);

            if (!empty($parsed['host'])) {
                if ($parsed['host'] !== $home_host) continue;
            } else {
                // ▼ 相対URL → 正規化
                if (strpos($url, '/') === 0) {
                    $url = rtrim($home_url, '/') . $url;
                } else {
                    continue;
                }
            }

            // ▼ フラグメントやクエリを除去
            $clean_url = preg_replace('/[#?].*/', '', $url);
            $post_id = url_to_postid($clean_url);

            if (!$post_id) {
                // fallback: path → post_name 逆引き
                $path = trim(wp_parse_url($clean_url, PHP_URL_PATH), '/');
                if ($path) {
                    $page = get_page_by_path($path, OBJECT, ['post', 'page']);
                    if ($page) {
                        $post_id = $page->ID;
                    }
                }
            }

            if ($post_id) {
                $links[] = (int) $post_id;
            }
        }

        return array_unique($links);
    }
}
// ファイル名: class-adbl-related-posts-query.php
// 


// ファイル名: class-adbl-batch-rebuilder.php


class AuroraDesignBlocks_RelatedPosts_BatchRebuilder
{
    private $db_manager;
    private $link_analyzer;

    public function __construct(
        AuroraDesignBlocks_RelatedPosts_DBManager $db_manager,
        AuroraDesignBlocks_RelatedPosts_LinkAnalyzer $link_analyzer
    ) {
        $this->db_manager   = $db_manager;
        $this->link_analyzer = $link_analyzer;
    }

    /**
     * 全投稿の内部リンクを再解析してリンクテーブルを再構築する
     *
     * @return array 結果情報
     */
    public function rebuild_all()
    {
        global $wpdb;
        // 処理件数カウンタ
        $processed = 0;
        $inserted  = 0;

        // 全投稿（post + page）
        $posts = $wpdb->get_results("
            SELECT ID, post_content
            FROM {$wpdb->posts}
            WHERE post_type IN ('post', 'page')
              AND post_status IN ('publish', 'draft', 'pending')
        ");

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // 1. 既存リンク削除
        $this->db_manager->truncate();
        foreach ($posts as $p) {
            $post_id = intval($p->ID);


            // 2. 内部リンク抽出（LinkAnalyzer の private メソッドを利用できないためラッパーを追加推奨）
            $targets = $this->link_analyzer->extract_internal_links($p->post_content);

            // 3. 挿入
            if (!empty($targets)) {
                $inserted += $this->db_manager->bulk_insert_links($post_id, $targets);
            }

            $processed++;
        }
        // デバッグ用にテーブル内容をログ出力
        $this->debug_dump_links();
        return [
            'processed_posts' => $processed,
            'inserted_links'  => $inserted,
        ];
    }


    private function debug_dump_links()
    {
        global $wpdb;

        $table = $this->db_manager->get_table_name();

        error_log('[Aurora RelatedPosts] ===== adb_links テーブル内容 START =====');

        $rows = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);

        if (empty($rows)) {
            error_log('[Aurora RelatedPosts] テーブルは空です。');
            return;
        }

        foreach ($rows as $row) {

            $source_id = intval($row['source_post_id']);
            $target_id = intval($row['target_post_id']);

            $source_title = get_the_title($source_id) ?: '(不明)';
            $target_title = get_the_title($target_id) ?: '(不明)';

            $output = [
                'sid' => $source_id,
                'st'   => $source_title,
                'tid' => $target_id,
                'tt'   => $target_title,
                'date'     => $row['updated_at'],
            ];
            //error_log(wp_json_encode($output, JSON_UNESCAPED_UNICODE));
            $output = [
                'tid' => $target_id,
                'tt'   => $target_title,
                'sid' => $source_id,
                'st'   => $source_title,
                'date'     => $row['updated_at'],
            ];
            error_log(wp_json_encode($output, JSON_UNESCAPED_UNICODE));
        }

        error_log('[Aurora RelatedPosts] ===== adb_links テーブル内容 END =====');


        error_log('[Aurora RelatedPosts] --- 相互リンク（被リンク元）一覧 START ---');

        // ▼ 相互リンク（target_post_id ごとの被リンク一覧）
        $unique_targets = array_unique(array_column($rows, 'target_post_id'));

        foreach ($unique_targets as $target_id) {
            $target_title = get_the_title($target_id) ?: '(不明)';

            $related = $this->db_manager->get_related_post_ids_and_scores($target_id, 9999);

            if (empty($related)) {
                continue;
            }

            foreach ($related as $r) {
                $source_title = get_the_title($r->related_id) ?: '(不明)';
                $score        = $r->score;

                error_log("[Reciprocal] {$source_title}  ==>  {$target_title} (score: {$score})");
            }
        }

        error_log('[Aurora RelatedPosts] --- 相互リンク（被リンク元）一覧 END ---');
    }
}

// add_action('init', function () {
//     //if (isset($_GET['adb_rebuild'])) {
//     $db = new AuroraDesignBlocks_RelatedPosts_DBManager($GLOBALS['wpdb']);
//     $analyzer = new AuroraDesignBlocks_RelatedPosts_LinkAnalyzer($db);
//     $rebuilder = new AuroraDesignBlocks_RelatedPosts_BatchRebuilder($db, $analyzer);

//     $rebuilder->rebuild_all();
//     //}
// });


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
        $show_thumb = isset($attributes['show_thumb']) ? $attributes['show_thumb'] : true;

        $show_excerpt = filter_var(isset($attributes['showExcerpt']) ? $attributes['showExcerpt'] : false, FILTER_VALIDATE_BOOLEAN);

        // SSRリクエストであるかを確認（エディターでのプレビュー時）
        $is_ssr_request = defined('REST_REQUEST') && REST_REQUEST;

        // IDの取得:
        // フロントエンドでは get_queried_object_id() を使用（ループ内外で信頼性が高い）
        // エディターでは get_the_ID() を使用（エディター独自のコンテキストに対応）
        $id_for_query = $is_ssr_request ? get_the_ID() : get_queried_object_id();
        $current_post_id = intval($id_for_query);


        // 1. フロントエンドでの表示条件チェック（SSRではない場合のみ）
        //目的: 投稿一覧ページ、アーカイブページ、ホームページなど、単一の記事ではない場所でブロックが誤って表示されるのを防ぎます。
        if (!$is_ssr_request) {
            // 単一ページではない、または有効な投稿IDがない場合はレンダリングしない
            // (get_queried_object_id()を使用しているため、サイドバーでもIDを取得可能)
            if (!is_singular() || $current_post_id === 0) {
                return '';
            }
        }

        // --- 2. エディタープレビュー時のガイドメッセージ (IDがない場合) ---
        // ここは $is_ssr_request=true(編集画面)かつcurrent_post_id=0 の場合に実行される。
        // 目的：ポストばまだ保存されていない状態でのエディタープレビューに対応するため。
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


            $thumb = '';
            if ($show_thumb) {
                $thumb_url = AuroraDesignBlocksPostThumbnail::getUrl($post, 'thumbnail');

                $thumb_html = sprintf(
                    '<img src="%s" loading="lazy" fetchpriority="low" alt="">',
                    esc_url($thumb_url)
                );

                // 画像は HTML を保持
                $thumb = wp_kses(
                    $thumb_html,
                    array(
                        'img' => array(
                            'src'             => true,
                            'loading'         => true,
                            'fetchpriority'   => true,
                            'alt'             => true,
                        ),
                    )
                );
            }

            $html .= sprintf(
                '<a href="%s">%s%s</a>',
                esc_url($link),
                $thumb,
                esc_html($title)
            );
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
        // 2. リンクテーブル更新（投稿保存時）
        // ※ save_postフックの防御的チェックは LinkAnalyzer クラス側で実装されるべきです。
        add_action('save_post', array($this->link_analyzer, 'update_related_links_on_save'), 10, 2);


        // 4. Gutenbergブロックの登録とフロントエンドレンダリング
        add_action('init', array($this, 'register_block'));

        // ★ 修正: 投稿最下部に関連記事を自動挿入
        // 無限ループ防止のため、優先度を非常に低く設定 (9999) することも可能だが、
        // ロジック内でフィルターを一時解除するアプローチを優先。
        add_filter('the_content', array($this, 'maybe_append_related_posts_to_content'), 10);


        // 🚨 修正点 1: アクティベーションフック内でグローバル変数と新規インスタンスを使用し、$thisへの依存を排除 
        register_activation_hook(ADB_PLUGIN_FILE, function () {
            global $wpdb;

            // 1. DBテーブル作成
            // 独立したDBManagerインスタンスを作成
            $db_manager_activation = new AuroraDesignBlocks_RelatedPosts_DBManager($wpdb);
            $db_manager_activation->create_links_table();

            // 2. スケジュール登録
            if (! wp_next_scheduled('AuroraDesignBlocks_rebuild_all_event')) {
                $timestamp = strtotime('03:00:00');
                if ($timestamp < time()) {
                    $timestamp = strtotime('tomorrow 03:00:00');
                }
                wp_schedule_event($timestamp, 'daily', 'AuroraDesignBlocks_rebuild_all_event');
            }
        });

        add_action('AuroraDesignBlocks_rebuild_all_event', function () {
            // この部分は既に独立したインスタンス生成を行っており問題ありません。
            global $wpdb;
            $db          = new AuroraDesignBlocks_RelatedPosts_DBManager($wpdb);
            $analyzer    = new AuroraDesignBlocks_RelatedPosts_LinkAnalyzer($db);
            $rebuilder   = new AuroraDesignBlocks_RelatedPosts_BatchRebuilder($db, $analyzer);
            $rebuilder->rebuild_all();
        });

        // デアクティベーションフックのファイルパスをADB_PLUGIN_FILEで統一
        register_deactivation_hook(ADB_PLUGIN_FILE, function () {
            $timestamp = wp_next_scheduled('AuroraDesignBlocks_rebuild_all_event');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'AuroraDesignBlocks_rebuild_all_event');
            }
        });
    }

    /**
     * Gutenbergブロックの登録（スタブ）
     */
    public function register_block()
    {
        // ... (省略: 変更なし)
        // 仮のブロック登録フック（実際のJS/JSONファイルが必要です）
        if (function_exists('register_block_type') && defined('AURORA_DESIGN_BLOCKS_PATH')) { // AURORA_DESIGN_BLOCKS_PATHの定義チェックを追加
            register_block_type(AURORA_DESIGN_BLOCKS_PATH . '/blocks/related-posts', array(
                'attributes' => array(
                    'limit' => array('type' => 'number', 'default' => 5),
                    'styleType' => array('type' => 'string', 'default' => 'list'),
                    'showExcerpt' => array('type' => 'boolean', 'default' => false),
                    'show_thumb' => array('type' => 'boolean', 'default' => false),
                ),
                'render_callback' => array($this->frontend, 'render_related_posts_block_html'),
            ));
        }
    }


    /**
     * 管理画面オプションがONなら投稿最下部に関連記事を追加
     */
    public function maybe_append_related_posts_to_content($content)
    {
        // 投稿ページでのみ適用
        if (!is_singular('post')) {
            return $content;
        }

        // 設定がONでない場合は何もしない
        if (get_option('aurora_related_posts_enable') !== '1') {
            return $content;
        }

        global $post;
        if (!$post) return $content;

        // ブロックとして表示済みの場合は二重表示を避けるため処理しない
        if (has_block('aurora-design-blocks/related-posts', $post)) {
            // return $content; // オリジナルの仕様維持のためコメントアウト状態を維持
        }

        // 🚨 修正点 2: 無限再帰防止のため、レンダリング前に一時的にこのフィルターを削除
        remove_filter('the_content', array($this, 'maybe_append_related_posts_to_content'), 10);

        // フロント用のレンダリングを呼び出す（SSRフラグは不要）
        $limit = get_option('aurora_related_posts_count', 5); // 管理画面設定を取得

        $show_thumb = get_option('aurora_related_posts_show_thumbnail', '1') === '1' ? true : false;
        $html = $this->frontend->render_related_posts_block_html(
            [
                'limit' => $limit,
                'show_thumb' => $show_thumb
            ], // SSRの attributes に件数を渡す
            ''
        );

        // 🚨 修正点 2: レンダリング後にフィルターを元に戻す
        add_filter('the_content', array($this, 'maybe_append_related_posts_to_content'), 10);

        return $content . $html;
    }
}

// プラグインの実行
new AuroraDesignBlocks_RelatedPosts_Plugin();
