<?php

/**
 * Class PopularPostsWidget_DisplayTest
 * 
 * @package Aurora_Design_Blocks
 */
class AuroraDesignBlocks_PopularPostsWidgetTest extends WP_UnitTestCase
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
     */
    public function test_widget_respects_days_limit()
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
     * @test サムネイルが表示されることを確認する
     */
    public function test_widget_displays_thumbnail_if_enabled()
    {
        // 投稿とサムネイル画像を作成
        $post_id = $this->create_post_with_views('Thumbnail Post', 15);
        $attachment_id = self::factory()->attachment->create_upload_object(
            DIR_TESTDATA . '/images/canola.jpg',
            $post_id
        );

        set_post_thumbnail($post_id, $attachment_id);

        $instance = [
            'title'          => '',
            'number'         => 1,
            'days'           => 30,
            'show_views'     => false,
            'show_thumbnail' => true,
        ];

        $output = $this->render_widget($instance);

        $this->assertStringContainsString("<img src='http://example.org/wp-content/uploads/2025/07/canola", $output);
        $this->assertStringContainsString('Thumbnail Post', $output);

        $instance = [
            'title'          => '',
            'number'         => 1,
            'days'           => 30,
            'show_views'     => false,
            'show_thumbnail' => false,
        ];

        $output = $this->render_widget($instance);

        $this->assertStringNotContainsString('<img', $output);
        $this->assertStringContainsString('Thumbnail Post', $output);
    }
}
