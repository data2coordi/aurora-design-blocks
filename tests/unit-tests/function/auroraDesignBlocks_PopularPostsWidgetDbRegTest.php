<?php

/**
 * Class PopularPostsWidget_DisplayTest
 *
 * @package Aurora_Design_Blocks
 */
class auroraDesignBlocks_PopularPostsWidgetDbRegTest extends WP_UnitTestCase
{

    /**
     * テスト前の初期化処理。
     * 必要な定数・ファイルの読み込みとテーブル作成を行う。
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('AURORA_DESIGN_BLOCKS_PATH')) {
            define('AURORA_DESIGN_BLOCKS_PATH', dirname(__DIR__, 2) . '/');
        }

        require_once AURORA_DESIGN_BLOCKS_PATH . 'inc/aurora-design-blocks-popularPosts.php';
        AuroraDesignBlocks_PostViewTracker::create_views_table();
    }


    /**
     * @test record_post_view() は新規レコードをDBに挿入する
     */
    public function test_record_post_view_inserts_new_record()
    {
        global $wpdb;

        // 投稿を新規作成
        $post_id = self::factory()->post->create();

        // テーブル名
        $table = $wpdb->prefix . 'auroradesignblocks_access_ct';

        // 現在日付
        $today = current_time('Y-m-d');

        // 事前に該当レコードが存在しないことを確認
        $count_before = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE post_id = %d AND view_date = %s", $post_id, $today)
        );
        $this->assertEquals(0, $count_before, '事前に該当レコードが存在しないこと');

        // record_post_viewを呼ぶ（新規挿入期待）
        AuroraDesignBlocks_PostViewTracker::record_post_view($post_id);

        // 挿入後、レコードが1件存在しview_countが1であることを確認
        $record = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE post_id = %d AND view_date = %s", $post_id, $today)
        );

        $this->assertNotNull($record, 'レコードがDBに挿入されていること');
        $this->assertEquals(1, $record->view_count, 'view_count が 1 であること');
    }

    /**
     * @test record_post_view() は既存レコードの view_count をインクリメントする
     */
    public function test_record_post_view_increments_existing_record()
    {
        global $wpdb;

        $post_id = self::factory()->post->create();
        $table = $wpdb->prefix . 'auroradesignblocks_access_ct';
        $today = current_time('Y-m-d');

        // 事前にレコードを1件挿入（view_count = 1）
        $wpdb->insert($table, [
            'post_id'    => $post_id,
            'view_date'  => $today,
            'view_count' => 1,
        ]);

        // record_post_viewを呼んでview_countが増えることを確認
        AuroraDesignBlocks_PostViewTracker::record_post_view($post_id);

        $record = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE post_id = %d AND view_date = %s", $post_id, $today)
        );

        $this->assertNotNull($record, 'レコードが存在すること');
        $this->assertEquals(2, $record->view_count, 'view_count がインクリメントされていること');
    }

    /**
     * @test record_post_view() は異なる日付で別レコードを追加する
     */
    /**
     * @test 異なる日付で同じ投稿IDの別レコードが作成されること
     */
    public function test_record_post_view_creates_separate_records_for_different_dates()
    {
        global $wpdb;

        $post_id = self::factory()->post->create();
        $table = $wpdb->prefix . 'auroradesignblocks_access_ct';

        $date1 = date('Y-m-d', strtotime('-1 day', current_time('timestamp')));
        $date2 = current_time('Y-m-d');

        // 日付1で直接DBにレコード挿入（view_count=1）
        $wpdb->insert($table, [
            'post_id'    => $post_id,
            'view_date'  => $date1,
            'view_count' => 1,
        ]);

        // record_post_viewを呼んで日付2の新規レコードを作成（view_count=1）
        // ただしrecord_post_viewは常に「今日の日付」で処理するのでdate2になる
        AuroraDesignBlocks_PostViewTracker::record_post_view($post_id);

        // date1のレコードが存在しview_count=1
        $record1 = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE post_id = %d AND view_date = %s", $post_id, $date1)
        );
        $this->assertNotNull($record1, 'date1のレコードが存在すること');
        $this->assertEquals(1, $record1->view_count, 'date1のview_countは1');

        // date2のレコードが存在しview_count=1
        $record2 = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE post_id = %d AND view_date = %s", $post_id, $date2)
        );
        $this->assertNotNull($record2, 'date2のレコードが存在すること');
        $this->assertEquals(1, $record2->view_count, 'date2のview_countは1');
    }


    /**
     * @test maybe_record_view はログイン中・Ajax・投稿ページ以外では処理されない
     */
    public function test_maybe_record_view_is_skipped_in_excluded_conditions()
    {
        $post_id = self::factory()->post->create();

        // record_post_view をモックして呼ばれたかをフラグで確認
        $called = false;
        add_filter('auroradesignblocks_mock_record_post_view', function () use (&$called) {
            $called = true;
        });

        // テスト対象をモック版に置き換え
        add_filter('auroradesignblocks_record_post_view_callable', function () {
            return function ($post_id) {
                do_action('auroradesignblocks_mock_record_post_view');
            };
        });

        // --- 3.3 ログイン中なら処理されない ---
        wp_set_current_user(self::factory()->user->create());
        AuroraDesignBlocks_PostViewTracker::maybe_record_view($post_id);
        $this->assertFalse($called, 'ログイン中は record_post_view が呼ばれない');
        wp_set_current_user(0); // ログアウト

        // --- 3.4 Ajaxリクエストなら処理されない ---
        $called = false;
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        AuroraDesignBlocks_PostViewTracker::maybe_record_view($post_id);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        $this->assertFalse($called, 'Ajaxリクエストでは record_post_view が呼ばれない');

        // --- 3.5 シングル投稿ページでなければ処理されない ---
        $called = false;
        // is_single() が false を返すようにフィルターで上書き
        add_filter('is_single', '__return_false');
        AuroraDesignBlocks_PostViewTracker::maybe_record_view($post_id);
        remove_filter('is_single', '__return_false');
        $this->assertFalse($called, '投稿ページ以外では record_post_view が呼ばれない');

        // フィルター解除
        remove_filter('auroradesignblocks_record_post_view_callable', '__return_false');
    }
}
