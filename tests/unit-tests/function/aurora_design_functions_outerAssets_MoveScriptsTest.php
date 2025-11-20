<?php // tests/unit-tests/auroraDesignBlocks_functions_outerAssets_AuroraDesignBlocksMoveScriptsTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/auroraDesignBlocks-functions-outerAssets.php';

// AURORA_DESIGN_BLOCKS_VERSION 定数が定義されていない場合に定義 (テスト環境用)
if (!defined('AURORA_DESIGN_BLOCKS_VERSION')) {
    define('AURORA_DESIGN_BLOCKS_VERSION', '1.0.1');
}

/**
 * AuroraDesignBlocksMoveScripts クラスのユニットテスト
 *
 * @coversDefaultClass AuroraDesignBlocksMoveScripts
 * @group assets
 * @group scripts
 */
class aurora_design_functions_outerAssets_MoveScriptsTest extends WP_UnitTestCase
{
    /**
     * テスト対象クラス名
     */
    private const TARGET_CLASS = AuroraDesignBlocksMoveScripts::class;

    /**
     * テスト対象の静的プロパティ名
     */
    private const SCRIPTS_PROPERTY = 'scripts';

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        parent::setUp();
        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // アクションフックを削除 (init() がグローバルスコープで呼ばれている可能性があるため)
        remove_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'move_scripts_to_footer'], 999);
        // WordPress のスクリプトキューをリセット
        $this->reset_scripts();
        // フィルターフックを再登録 (テスト対象のメソッドを確実にフックするため)
        AuroraDesignBlocksMoveScripts::init();

        // 管理画面フラグをリセット
        set_current_screen('front'); // デフォルトはフロントエンド
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // アクションフックを削除
        remove_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'move_scripts_to_footer'], 999);
        // WordPress のスクリプトキューをリセット
        $this->reset_scripts();
        // 管理画面フラグをリセット
        set_current_screen('front');
        parent::tearDown();
    }

    /**
     * WordPress のスクリプトキューをリセットするヘルパーメソッド
     */
    private function reset_scripts(): void
    {
        global $wp_scripts;
        // 常に新しいインスタンスで上書きして完全にリセット
        $wp_scripts = new WP_Scripts();
        // 必要に応じてデフォルトスクリプトを再登録
        // wp_default_scripts($wp_scripts);
    }

    /**
     * Reflection を使用して静的プロパティの値を設定するヘルパーメソッド
     *
     * @param mixed $value 設定する値
     */
    private function set_static_property_value($value): void
    {
        try {
            $reflection = new ReflectionProperty(self::TARGET_CLASS, self::SCRIPTS_PROPERTY);
            // PHP 8.1 以降では setAccessible は不要な場合があるが、互換性のために残す
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, $value); // 静的プロパティを設定
        } catch (ReflectionException $e) {
            $this->fail("Failed to set static property " . self::TARGET_CLASS . "::" . self::SCRIPTS_PROPERTY . ": " . $e->getMessage());
        }
    }

    /**
     * Reflection を使用して静的プロパティの値を取得するヘルパーメソッド
     *
     * @return mixed 静的プロパティの値
     */
    private function get_static_property_value()
    {
        try {
            $reflectionClass = new ReflectionClass(self::TARGET_CLASS);
            $property = $reflectionClass->getProperty(self::SCRIPTS_PROPERTY);
            // PHP 8.1 以降では setAccessible は不要な場合があるが、互換性のために残す
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }
            return $property->getValue();
        } catch (ReflectionException $e) {
            $this->fail("Failed to get static property " . self::TARGET_CLASS . "::" . self::SCRIPTS_PROPERTY . ": " . $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::init
     * init() メソッドが wp_enqueue_scripts アクションを正しい優先度で登録するかテスト
     */
    public function test_init_adds_wp_enqueue_scripts_action_with_correct_priority(): void
    {
        // Arrange: setUp でフックが登録されているはず
        // Act: なし
        // Assert: フィルターが正しい優先度で登録されているか確認
        $this->assertEquals(999, has_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'move_scripts_to_footer']));
    }

    /**
     * @test
     * @covers ::add_scripts
     * 単一のスクリプトハンドルとパスを追加できるかテスト
     */
    public function test_add_scripts_adds_single_script(): void
    {
        // Arrange
        $scripts_to_add = ['my-script' => '/path/to/my-script.js'];

        // Act
        AuroraDesignBlocksMoveScripts::add_scripts($scripts_to_add);

        // Assert
        $added_scripts = $this->get_static_property_value();
        $this->assertEquals($scripts_to_add, $added_scripts);
    }

    /**
     * @test
     * @covers ::add_scripts
     * 複数のスクリプトハンドルとパスを追加できるかテスト
     */
    public function test_add_scripts_adds_multiple_scripts(): void
    {
        // Arrange
        $scripts_to_add = [
            'script-1' => '/path/to/script-1.js',
            'script-2' => '/path/to/script-2.js',
        ];

        // Act
        AuroraDesignBlocksMoveScripts::add_scripts($scripts_to_add);

        // Assert
        $added_scripts = $this->get_static_property_value();
        $this->assertEquals($scripts_to_add, $added_scripts);
    }

    /**
     * @test
     * @covers ::add_scripts
     * 既存のリストに新しいスクリプトを追加できるかテスト
     */
    public function test_add_scripts_appends_to_existing(): void
    {
        // Arrange
        $initial_scripts = ['existing-script' => '/path/to/existing.js'];
        $this->set_static_property_value($initial_scripts);
        $scripts_to_add = ['new-script' => '/path/to/new.js'];
        $expected_scripts = array_merge($initial_scripts, $scripts_to_add);

        // Act
        AuroraDesignBlocksMoveScripts::add_scripts($scripts_to_add);

        // Assert
        $added_scripts = $this->get_static_property_value();
        $this->assertEquals($expected_scripts, $added_scripts);
    }

    /**
     * @test
     * @covers ::add_scripts
     * 同じハンドルでスクリプトを追加した場合に上書きされるかテスト
     */
    public function test_add_scripts_overwrites_existing_handle(): void
    {
        // Arrange
        $initial_scripts = ['my-script' => '/path/to/old.js'];
        $this->set_static_property_value($initial_scripts);
        $scripts_to_add = ['my-script' => '/path/to/new.js']; // 同じハンドル
        $expected_scripts = $scripts_to_add; // 上書きされることを期待

        // Act
        AuroraDesignBlocksMoveScripts::add_scripts($scripts_to_add);

        // Assert
        $added_scripts = $this->get_static_property_value();
        $this->assertEquals($expected_scripts, $added_scripts);
    }

    /**
     * @test
     * @covers ::move_scripts_to_footer
     * フロントエンドで、指定したスクリプトがフッターに移動されるかテスト
     */
    public function test_move_scripts_to_footer_moves_script_on_frontend(): void
    {
        // Arrange
        $handle = 'my-movable-script';
        $path = '/js/movable.js';
        $full_path = includes_url($path); // includes_url を使う場合
        // $full_path = content_url('themes/auroraDesignBlocks' . $path); // テーマディレクトリからのパスの場合

        // 1. 移動対象のスクリプトを事前にヘッダーで登録・エンキューしておく
        wp_register_script($handle, $full_path, [], '1.0', false); // in_footer = false (ヘッダー)
        wp_enqueue_script($handle);

        // 2. AuroraDesignBlocksMoveScripts に移動対象として登録
        AuroraDesignBlocksMoveScripts::add_scripts([$handle => $full_path]); // パスも渡す

        // 3. ヘッダーでエンキューされていることを確認
        $this->assertTrue(wp_script_is($handle, 'enqueued'), "Script '{$handle}' should be enqueued initially.");
        $script_obj_before = $this->get_script_object($handle);
        $this->assertNotNull($script_obj_before, "Script object for '{$handle}' should exist before move.");
        // group が false (ヘッダー) であることを確認 (修正箇所)
        $this->assertFalse(isset($script_obj_before->extra['group']) && $script_obj_before->extra['group'] === 1, "Script '{$handle}' should be in header initially (group is not set or not 1).");


        // Act: wp_enqueue_scripts アクションを実行 (これにより move_scripts_to_footer が呼ばれる)
        do_action('wp_enqueue_scripts');

        // Assert: スクリプトがフッターに移動されたか確認
        $this->assertTrue(wp_script_is($handle, 'enqueued'), "Script '{$handle}' should still be enqueued after move.");

        $script_obj_after = $this->get_script_object($handle);
        $this->assertNotNull($script_obj_after, "Script object for '{$handle}' should exist after move.");

        // group が 1 (フッター) になっていることを確認
        $this->assertTrue(isset($script_obj_after->extra['group']) && $script_obj_after->extra['group'] === 1, "Script '{$handle}' should be moved to footer (group===1).");
        // バージョンや依存関係が維持されているかも確認 (オプション)

        $this->assertEquals(AURORA_DESIGN_BLOCKS_VERSION, $script_obj_after->ver, "Script '{$handle}' version should be maintained.");

        // $this->assertEquals([], $script_obj_after->deps, "Script '{$handle}' dependencies should be maintained.");
    }

    /**
     * @test
     * @covers ::move_scripts_to_footer
     * 管理画面ではスクリプトが移動されないことをテスト
     */
    public function test_move_scripts_to_footer_does_not_move_script_in_admin(): void
    {
        // Arrange
        set_current_screen('dashboard'); // 管理画面に設定

        $handle = 'my-admin-script';
        $path = '/js/admin.js';
        $full_path = includes_url($path);

        // 1. 移動対象のスクリプトを事前にヘッダーで登録・エンキュー
        wp_register_script($handle, $full_path, [], '1.0', false);
        wp_enqueue_script($handle);

        // 2. AuroraDesignBlocksMoveScripts に登録
        AuroraDesignBlocksMoveScripts::add_scripts([$handle => $full_path]);

        // 3. ヘッダーでエンキューされていることを確認
        $this->assertTrue(wp_script_is($handle, 'enqueued'), "Script '{$handle}' should be enqueued initially in admin.");
        $script_obj_before = $this->get_script_object($handle);
        $this->assertNotNull($script_obj_before, "Script object for '{$handle}' should exist before hook in admin.");
        // group が false (ヘッダー) であることを確認 (修正箇所)
        $this->assertFalse(isset($script_obj_before->extra['group']) && $script_obj_before->extra['group'] === 1, "Script '{$handle}' should be in header initially in admin (group is not set or not 1).");

        // Act: wp_enqueue_scripts アクションを実行
        do_action('wp_enqueue_scripts');

        // Assert: スクリプトが移動されていない（ヘッダーのまま）ことを確認
        $this->assertTrue(wp_script_is($handle, 'enqueued'), "Script '{$handle}' should remain enqueued in admin.");
        $script_obj_after = $this->get_script_object($handle);
        $this->assertNotNull($script_obj_after, "Script object for '{$handle}' should exist after hook in admin.");
        // group が 1 (フッター) になっていないことを確認
        $this->assertFalse(isset($script_obj_after->extra['group']) && $script_obj_after->extra['group'] === 1, "Script '{$handle}' should NOT be moved to footer in admin (group!=1).");
    }

    /**
     * @test
     * @covers ::move_scripts_to_footer
     * スクリプトが追加されていない場合、何も起こらないことをテスト
     */
    public function test_move_scripts_to_footer_does_nothing_when_no_scripts_added(): void
    {
        // Arrange
        $handle = 'some-other-script';
        $path = '/js/other.js';
        $full_path = includes_url($path);

        // 1. 何か別のスクリプトをエンキューしておく
        wp_register_script($handle, $full_path, [], '1.0', false);
        wp_enqueue_script($handle);

        // 2. AuroraDesignBlocksMoveScripts には何も登録しない
        // $this->set_static_property_value([]); // setUp でリセット済み

        // 3. ヘッダーでエンキューされていることを確認
        $this->assertTrue(wp_script_is($handle, 'enqueued'));
        $script_obj_before = $this->get_script_object($handle);
        // group が false (ヘッダー) であることを確認 (修正箇所)
        $this->assertFalse(isset($script_obj_before->extra['group']) && $script_obj_before->extra['group'] === 1);

        // Act: wp_enqueue_scripts アクションを実行
        do_action('wp_enqueue_scripts');

        // Assert: スクリプトの状態が変わっていないことを確認
        $this->assertTrue(wp_script_is($handle, 'enqueued'));
        $script_obj_after = $this->get_script_object($handle);
        $this->assertFalse(isset($script_obj_after->extra['group']) && $script_obj_after->extra['group'] === 1);
        // 念のため、登録されているスクリプトのリストが変更されていないか確認
        // (これは少し難しいので、特定のスクリプトの状態確認で代用)
    }

    /**
     * ヘルパーメソッド: 指定されたハンドルのスクリプトオブジェクトを取得
     * @param string $handle スクリプトハンドル
     * @return _WP_Dependency|null スクリプトオブジェクト or null
     */
    private function get_script_object(string $handle): ?_WP_Dependency
    {
        global $wp_scripts;
        if (isset($wp_scripts) && $wp_scripts instanceof WP_Scripts) {
            return $wp_scripts->query($handle);
        }
        return null;
    }
}
