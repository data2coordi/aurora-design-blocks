<?php

/**
 * Class PopularPostsWidget_DisplayTest
 * 
 * @package Aurora_Design_Blocks
 */
class auroraDesignBlocks_PopularPostsWidgetTest extends WP_UnitTestCase
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
     * 投稿を作成し、指定されたビュー数でアクセスカウントを挿入する。
     *
     * @param string $title 投稿タイトル
     * @param int $views ビュー数
     * @param string|null $date 日付（null の場合は現在日）
     * @return int 作成された投稿ID
     */
    private function create_post_with_views(string $title, int $views, ?string $date = null): int
    {
        $post_id = self::factory()->post->create(['post_title' => $title]);
        global $wpdb;
        $table = $wpdb->prefix . 'auroradesignblocks_access_ct';
        $date = $date ?? current_time('Y-m-d');

        $wpdb->insert($table, [
            'post_id'    => $post_id,
            'view_date'  => $date,
            'view_count' => $views,
        ]);

        return $post_id;
    }

    /*同じpostidで複数日のデータを作成する*/
    protected function create_post_with_views_over_days($title, $views_per_day_map)
    {
        $post_id = $this->factory->post->create(['post_title' => $title]);

        global $wpdb;
        $table = $wpdb->prefix . 'auroradesignblocks_access_ct';

        foreach ($views_per_day_map as $days_ago => $views) {
            $date = date('Y-m-d', strtotime("-{$days_ago} days", current_time('timestamp')));

            $wpdb->insert($table, [
                'post_id'    => $post_id,
                'view_date'  => $date,
                'view_count' => $views,
            ]);
        }

        return $post_id;
    }

    /**
     * ウィジェットをレンダリングし、その出力文字列を返す。
     *
     * @param array $instance ウィジェットのインスタンス設定
     * @param array $args 表示用の引数（HTML構造）
     * @return string ウィジェットのHTML出力
     */
    private function render_widget(array $instance, array $args = []): string
    {
        $widget = new AuroraDesignBlocks_Popular_Posts_Widget();
        $default_args = [
            'before_widget' => '',
            'after_widget'  => '',
            'before_title'  => '',
            'after_title'   => '',
        ];

        $args = array_merge($default_args, $args);

        ob_start();
        $widget->widget($args, $instance);
        return ob_get_clean();
    }

    /**
     * @test 表示件数1件（最小）でのウィジェット出力が正しく行われる
        1.1 表示件数が正しいこと
     */
    public function test_widget_renders_one_popular_post()
    {
        $this->create_post_with_views('Test Post', 10);

        $instance = [
            'title'          => 'Popular',
            'number'         => 1,
            'days'           => 30,
            'show_views'     => true,
            'show_thumbnail' => false,
        ];

        $args = [
            'before_widget' => '<div class="widget">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2>',
            'after_title'   => '</h2>',
        ];

        $output = $this->render_widget($instance, $args);

        $this->assertStringContainsString('Test Post', $output);
        $this->assertStringContainsString('(10)', $output);
    }

    /**
     * @test 表示件数の上限を999にしてもエラーにならず、既存投稿が正しく表示される
     * 1.1 表示件数が正しいこと
     */
    public function test_widget_renders_with_large_number_limit()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->create_post_with_views("Post {$i}", 1 + $i);
        }

        $instance = [
            'title'          => '',
            'number'         => 999,
            'days'           => 30,
            'show_views'     => false,
            'show_thumbnail' => false,
        ];

        $output = $this->render_widget($instance);

        for ($i = 0; $i < 5; $i++) {
            $this->assertStringContainsString("Post {$i}", $output);
        }
    }

    /**
     * @test 集計期間1日を指定したとき、期間外の投稿が表示されない
     * 1.2 集計期間が正しいこと
     */
    public function test_widget_respects_days_no_targetData()
    {
        $past_date = date('Y-m-d', strtotime('-2 days', current_time('timestamp')));
        $this->create_post_with_views('Recent Post', 100, $past_date);

        $instance = [
            'title'          => '',
            'number'         => 5,
            'days'           => 1,
            'show_views'     => true,
            'show_thumbnail' => false,
        ];

        $output = $this->render_widget($instance);

        $this->assertStringNotContainsString('Recent Post', $output);
        $this->assertStringContainsString('No popular posts yet.', $output);
    }

    /**
     * @test 過去5日分のデータが存在し、集計期間3日を指定したとき、古い2件は表示されない
     * 1.2 集計期間が正しく反映されていること
     */
    public function test_widget_respects_days()
    {
        // 各日ごとに異なる投稿を作成（古い順）
        $this->create_post_with_views('Post 5 days ago', 50, date('Y-m-d', strtotime('-5 days', current_time('timestamp'))));
        $this->create_post_with_views('Post 4 days ago', 60, date('Y-m-d', strtotime('-4 days', current_time('timestamp'))));
        $this->create_post_with_views('Post 3 days ago', 70, date('Y-m-d', strtotime('-3 days', current_time('timestamp'))));
        $this->create_post_with_views('Post 2 days ago', 80, date('Y-m-d', strtotime('-2 days', current_time('timestamp'))));
        $this->create_post_with_views('Post 1 day ago', 90, date('Y-m-d', strtotime('-1 day', current_time('timestamp'))));

        // 集計期間を「過去3日間」に限定（＝1〜3日前の投稿が対象）
        $instance = [
            'title'          => '',
            'number'         => 5,
            'days'           => 3,
            'show_views'     => true,
            'show_thumbnail' => false,
        ];

        $output = $this->render_widget($instance);

        // 表示されるべき投稿（3日以内）
        $this->assertStringContainsString('Post 3 days ago', $output);
        $this->assertStringContainsString('Post 2 days ago', $output);
        $this->assertStringContainsString('Post 1 day ago', $output);

        // 表示されないべき投稿（4日以上前）
        $this->assertStringNotContainsString('Post 4 days ago', $output);
        $this->assertStringNotContainsString('Post 5 days ago', $output);
    }

    /**
     * @test 過去5日分のデータが存在し、pv件数が正しいこと
     * 1.3 pv件数が正しいこと
     */
    public function test_widget_respects_pv()
    {
        // 各日ごとに異なる投稿を作成（古い順）
        $this->create_post_with_views_over_days('Post 10pv', [5 => 6, 4 => 4]);
        $this->create_post_with_views_over_days('Post 20pv', [3 => 10, 2 => 10]);
        // 集計期間を「過去3日間」に限定（＝1〜3日前の投稿が対象）
        $instance = [
            'title'          => '',
            'number'         => 5,
            'days'           => 5,
            'show_views'     => true,
            'show_thumbnail' => false,
        ];

        $output = $this->render_widget($instance);

        // 表示されるべき投稿（3日以内）
        $this->assertStringContainsString('Post 10pv (10)', $output);
        $this->assertStringContainsString('Post 20pv (20)', $output);
    }


    /**
     * @test サムネイル、PVが表示されることを確認する
     * 1.3 pv件数が正しいこと
     * 1.4 サムネイル表示有無がただしいこと
     */
    public function test_widget_displays_thumbnail_if_enabled()
    {

        add_filter('upload_dir', function ($dirs) {
            $dirs['subdir'] = '/2025/07';
            $dirs['path'] = $dirs['basedir'] . $dirs['subdir'];
            $dirs['url'] = $dirs['baseurl'] . $dirs['subdir'];
            return $dirs;
        });

        // 投稿とサムネイル画像を作成
        $post_id = $this->create_post_with_views('Thumbnail pv Post', 15);
        $attachment_id = self::factory()->attachment->create_upload_object(
            DIR_TESTDATA . '/images/canola.jpg',
            $post_id
        );

        set_post_thumbnail($post_id, $attachment_id);

        $instance = [
            'title'          => '',
            'number'         => 1,
            'days'           => 30,
            'show_views'     => true,
            'show_thumbnail' => true,
        ];

        $output = $this->render_widget($instance);


        /*サムネイル表示されるパターン*/
        $this->assertStringContainsString("<img src='http://example.org/wp-content/uploads/2025/07/canola", $output);
        $this->assertStringContainsString('Thumbnail pv Post (15)', $output);

        $instance = [
            'title'          => '',
            'number'         => 1,
            'days'           => 30,
            'show_views'     => false,
            'show_thumbnail' => false,
        ];

        $output = $this->render_widget($instance);

        /*サムネイル表示されないパターン*/
        $this->assertStringNotContainsString('<img', $output);
        $this->assertStringNotContainsString('Thumbnail pv Post (15)', $output);
        $this->assertStringContainsString('Thumbnail pv Post', $output);

        remove_all_filters('upload_dir'); // あるいは remove_filter() で個別に外してもOK

    }

    /**
     * @test 保存時に「キャッシュを削除」が有効な場合、該当キャッシュが削除される
     */
    public function test_cache_is_cleared_on_update_when_option_checked()
    {
        // 初期状態でキャッシュをセット
        $days = 3;
        $limit = 5;
        $cache_key = "adb_popular_posts_{$days}_{$limit}";
        set_transient($cache_key, 'dummy_data', 5 * MINUTE_IN_SECONDS);

        $this->assertNotFalse(get_transient($cache_key), '事前キャッシュが存在すること');

        // ウィジェットのインスタンスを生成
        $widget = new AuroraDesignBlocks_Popular_Posts_Widget();

        // キャッシュクリアが有効な更新リクエストを作成
        $new_instance = [
            'title'          => '',
            'number'         => $limit,
            'days'           => $days,
            'show_views'     => true,
            'show_thumbnail' => false,
            'clear_cache'    => true, // ← これがトリガー
        ];

        $old_instance = [];

        // update() を実行（保存処理）
        $widget->update($new_instance, $old_instance);

        // キャッシュが削除されていることを確認
        $this->assertFalse(get_transient($cache_key), '保存後にキャッシュが削除されていること');
    }


    /**************************************************************************      */
    /**************************************************************************      */
    /*DBキャッシュのテスト      */
    /**************************************************************************      */
    /**************************************************************************      */


    /**
     * @test キャッシュが存在する場合、DBクエリを呼ばずにキャッシュ結果が返るか
     * 2.1 キャッシュから取得されること 
     */
    public function test_get_popular_posts_by_days_uses_cache()
    {
        global $query_executed;

        // クエリ実行フラグ初期化
        $query_executed = false;

        // クエリ実行フラグを立てる関数を定義
        function set_query_executed_flag($query)
        {
            global $query_executed;
            $query_executed = true;
            return $query;
        }

        // フィルター登録
        add_filter('query', 'set_query_executed_flag');

        // まずキャッシュ削除（確実に初回はクエリが実行されるように）
        delete_transient('adb_popular_posts_30_5');

        // 投稿データ作成（ビュー付き）
        $post_id = $this->create_post_with_views('Cache Test Post', 42);

        // 初回：キャッシュ作成のためDBクエリが走る
        $results1 = AuroraDesignBlocks_Popular_Posts_Widget::get_popular_posts_by_days(30, 5);
        $this->assertNotEmpty($results1);
        $this->assertEquals($post_id, $results1[0]['post_id']);
        $this->assertEquals(42, $results1[0]['views']);

        // クエリ実行済みであることを確認
        $this->assertTrue($query_executed, '初回はクエリが実行される');

        // フラグをリセット
        $query_executed = false;

        // 2回目：キャッシュが使われるならクエリは発行されない
        $results2 = AuroraDesignBlocks_Popular_Posts_Widget::get_popular_posts_by_days(30, 5);

        // クエリが発行されていないことを確認
        $this->assertFalse($query_executed, 'キャッシュ使用時はクエリが発行されない');

        // 結果が一致していることも確認
        $this->assertEquals($results1, $results2);

        // フィルターを削除（テスト汚染防止）

        remove_filter('query', 'set_query_executed_flag');
    }



    /**
     * @test キャッシュは5分経つと無効になり、再取得時に再セットされる
     * 2.2 ５分に一回リフレッシュされること。
     */

    public function test_cache_expiration_emulation()
    {
        global $wpdb;

        // 事前にキャッシュ削除
        delete_transient('adb_popular_posts_30_5');
        $this->assertFalse(get_transient('adb_popular_posts_30_5'), '事前にキャッシュなし');

        // 投稿とビューを作成
        $this->create_post_with_views('Expire Cache Post', 10);

        // 初回取得でキャッシュセット
        $first = AuroraDesignBlocks_Popular_Posts_Widget::get_popular_posts_by_days(30, 5);
        $this->assertNotEmpty($first, '初回取得でデータあり');
        $this->assertNotFalse(get_transient('adb_popular_posts_30_5'), 'キャッシュがセットされている');

        // キャッシュの期限切れをDBのオプションを直接更新してエミュレート
        $timeout_key = '_transient_timeout_adb_popular_posts_30_5';
        $wpdb->update(
            $wpdb->options,
            ['option_value' => time() - 1],  // 期限切れを過去にセット
            ['option_name' => $timeout_key]
        );

        /*オブジェクトキャッシュクリア*/
        wp_cache_delete('adb_popular_posts_30_5', 'options');
        wp_cache_delete('_transient_timeout_adb_popular_posts_30_5', 'options');

        // get_transientはfalseを返すはず（期限切れ検知）
        $expired_cache = get_transient('adb_popular_posts_30_5');
        $this->assertFalse($expired_cache, 'キャッシュは期限切れとして認識される');

        // 再取得でキャッシュが再セットされる
        $second = AuroraDesignBlocks_Popular_Posts_Widget::get_popular_posts_by_days(30, 5);
        $this->assertNotEmpty($second, '再取得でデータあり');
        $this->assertNotFalse(get_transient('adb_popular_posts_30_5'), '再度キャッシュがセットされている');
    }













    /**
     * @test パラメータごとに別々のキャッシュが管理されている
     * 2.3 設定値によってキャッシュデータは変更されること
     */
    public function test_cache_key_varies_with_parameters()
    {
        // 事前に両トランジェントをクリア
        delete_transient('adb_popular_posts_10_5');
        delete_transient('adb_popular_posts_30_5');

        // テスト用投稿＋ビュー数挿入（どちらの期間にも含まれるデータ）
        $this->create_post_with_views('Cache Param Post', 20);

        // 10日間集計、5件取得 → キャッシュセット
        $results10 = AuroraDesignBlocks_Popular_Posts_Widget::get_popular_posts_by_days(10, 5);
        $this->assertNotEmpty($results10);
        $this->assertNotFalse(
            get_transient('adb_popular_posts_10_5'),
            '10日間用キャッシュがセットされていること'
        );

        // 30日間集計、5件取得 → 別キーでキャッシュセット
        $results30 = AuroraDesignBlocks_Popular_Posts_Widget::get_popular_posts_by_days(30, 5);
        $this->assertNotEmpty($results30);
        $this->assertNotFalse(
            get_transient('adb_popular_posts_30_5'),
            '30日間用キャッシュがセットされていること'
        );

        // トランジェントの中身は同じでもキーが別であることの確認
        $this->assertNotSame(
            get_transient('adb_popular_posts_10_5'),
            false,
            '10日間用キャッシュは false ではない'
        );
        $this->assertNotSame(
            get_transient('adb_popular_posts_30_5'),
            false,
            '30日間用キャッシュは false ではない'
        );
    }
}


/* テストケース一覧 */
/*
1 機能テスト
1.1 表示件数が正しいこと
1.2 集計期間が正しいこと
1.3 pv件数が正しいこと
1.4 サムネイル表示有無がただしいこと
1.5 キャッシュクリアが正常に機能すること

2 キャッシュテスト
2.1 キャッシュから取得されること 
2.2 ５分に一回リフレッシュされること。
2.3 設定値によってキャッシュデータは変更されること
 */