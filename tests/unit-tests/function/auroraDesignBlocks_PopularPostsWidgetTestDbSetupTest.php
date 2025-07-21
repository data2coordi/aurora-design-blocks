<?php

class auroraDesignBlocks_PopularPostsWidgetTestDbSetupTest extends WP_UnitTestCase
{
    protected $table_name;

    public function setUp(): void
    {
        parent::setUp();
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'auroradesignblocks_access_ct';

        // テーブル削除しておく
        $wpdb->query("DROP TABLE IF EXISTS {$this->table_name}");
    }

    public function tearDown(): void
    {
        // クリーンアップ
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$this->table_name}");
        parent::tearDown();
    }

    /**
     * @test ウィジェット更新処理を通してテーブルが作成されること
     */
    public function test_table_is_created_via_widget_update()
    {
        global $wpdb;

        // テーブル未作成であることを確認
        $this->assertNotEquals(
            $this->table_name,
            $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'"),
            '前提: テーブルが存在しない'
        );

        if (!defined('AURORA_DESIGN_BLOCKS_PATH')) {
            define('AURORA_DESIGN_BLOCKS_PATH', dirname(__DIR__, 2) . '/');
        }


        // ウィジェットクラスを直接インスタンス化
        require_once AURORA_DESIGN_BLOCKS_PATH . 'inc/aurora-design-blocks-popularPosts.php';
        $widget = new AuroraDesignBlocks_Popular_Posts_Widget();

        // update() を実行（通常管理画面で「保存」されたときの挙動）
        $dummy_old_instance = [];
        $dummy_new_instance = [
            'title'          => 'テスト人気記事',
            'number'         => 3,
            'days'           => 7,
            'show_views'     => 1,
            'show_thumbnail' => 1,
            'clear_cache'    => 0,
        ];
        $widget->update($dummy_new_instance, $dummy_old_instance);


        // → ここから後者対応
        // (1) DESCRIBE で列情報が取れるか確認
        $columns = $wpdb->get_results("DESCRIBE {$this->table_name}");
        $this->assertNotEmpty(
            $columns,
            "{$this->table_name} が存在しないか、DESCRIBE で取得できませんでした"
        );

        // (2) SHOW CREATE TABLE でも念のためチェック
        $create = $wpdb->get_row("SHOW CREATE TABLE `{$this->table_name}`", ARRAY_N);
        $this->assertNotEmpty(
            $create,
            "{$this->table_name} の CREATE 文が取得できませんでした"
        );
    }
}
