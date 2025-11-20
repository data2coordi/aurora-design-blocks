<?php // tests/unit-tests/AuroraDesignBlocksEditorScriptsTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/AuroraDesignBlocks-functions-outerAssets.php';
// require_once dirname(__DIR__, 2) . '/inc/AuroraDesignBlocks-functions-block.php'; // 依存関係がある場合

// AURORA_DESIGN_BLOCKS_VERSION 定数が定義されていない場合に定義 (テスト環境用)
if (!defined('AURORA_DESIGN_BLOCKS_VERSION')) {
    define('AURORA_DESIGN_BLOCKS_VERSION', '1.0.0');
}

/**
 * AuroraDesignBlocksEditorScripts クラスのユニットテスト (シンプル版)
 *
 * @coversDefaultClass AuroraDesignBlocksEditorScripts
 * @group assets
 * @group scripts
 * @group editor
 */
class aurora_design_functions_outerAssets_EditorScriptsTest extends WP_UnitTestCase // クラス名を修正 (PSR-4推奨) AuroraDesignBlocksEditorScriptsTest
{
    /**
     * テスト対象クラス名
     */
    private const TARGET_CLASS = AuroraDesignBlocksEditorScripts::class;

    /**
     * テスト対象の静的プロパティ名
     */
    private const SCRIPTS_PROPERTY = 'scripts';

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        // ★★★ parent::setUp() の前にフックを削除 ★★★
        // これにより、parent::setUp() 内で after_setup_theme が実行されても
        // AuroraDesignBlocksCommonJsAssets::init が呼び出されるのを防ぐ
        remove_action('after_setup_theme', ['AuroraDesignBlocksCommonJsAssets', 'init']);
        // ★★★ AuroraDesignBlocks-functions-block.php 内のフックも削除 ★★★
        // AuroraDesignBlocks_add_fontawesome_button_to_toolbar() が add_scripts を呼ぶため
        // この関数自体を削除するか、関数内の add_scripts 呼び出しをテスト中に無効化する必要がある
        // ここでは関数自体を削除するアプローチをとる (より確実な方法はモックライブラリの使用)
        // ※ ただし、この関数が他のテストに必要なら、より洗練された方法が必要
        // remove_action('init', 'AuroraDesignBlocks_add_fontawesome_button_to_toolbar'); // init フックではない可能性あり、要確認

        parent::setUp(); // parent::setUp() を後に移動

        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // アクションフックを削除 (enqueue_block_editor_assets)
        remove_action('enqueue_block_editor_assets', [self::TARGET_CLASS, 'enqueue_editor_scripts']);
        // WordPress のスクリプトキューをリセット
        $this->reset_scripts();
        // 再度静的プロパティをリセット (念のため)
        $this->set_static_property_value([]);

        // 依存クラス (AuroraDesignBlocksDeferJs) のプロパティもリセット
        $this->reset_defer_js_property();
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // アクションフックを削除
        remove_action('enqueue_block_editor_assets', [self::TARGET_CLASS, 'enqueue_editor_scripts']);
        // WordPress のスクリプトキューをリセット
        $this->reset_scripts();

        // 依存クラス (AuroraDesignBlocksDeferJs) のプロパティもリセット
        $this->reset_defer_js_property();

        // ★★★ after_setup_theme フックを元に戻す (他のテストに影響を与えないように) ★★★
        add_action('after_setup_theme', ['AuroraDesignBlocksCommonJsAssets', 'init']);
        // ★★★ AuroraDesignBlocks-functions-block.php 内のフックも元に戻す ★★★
        // add_action('init', 'AuroraDesignBlocks_add_fontawesome_button_to_toolbar'); // init フックではない可能性あり

        parent::tearDown();
    }

    /**
     * WordPress のスクリプトキューをリセットするヘルパーメソッド
     */
    private function reset_scripts(): void
    {
        global $wp_scripts;
        // ★★★ 修正: 強制的に新しいインスタンスで上書き ★★★
        $wp_scripts = new WP_Scripts();
        // wp_default_scripts($wp_scripts); // 必要に応じてデフォルトスクリプトを再登録
    }

    /**
     * AuroraDesignBlocksDeferJs の静的プロパティをリセットするヘルパーメソッド
     */
    private function reset_defer_js_property(): void
    {
        try {
            // AuroraDesignBlocksDeferJs クラスが存在するか確認
            if (!class_exists('AuroraDesignBlocksDeferJs')) {
                // クラスが存在しない場合は何もしないか、エラーを出す
                // $this->markTestSkipped('AuroraDesignBlocksDeferJs class not found.');
                return;
            }
            $reflection = new ReflectionProperty(AuroraDesignBlocksDeferJs::class, 'deferred_scripts');
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, []); // 静的プロパティを空配列にリセット
        } catch (ReflectionException $e) {
            // プロパティが存在しない場合などのエラー処理
            $this->fail("Failed to reset static property AuroraDesignBlocksDeferJs::deferred_scripts: " . $e->getMessage());
        }
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
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, $value); // 静的プロパティなので第一引数は null
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
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }
            return $property->getValue(null);
        } catch (ReflectionException $e) {
            $this->fail("Failed to get static property " . self::TARGET_CLASS . "::" . self::SCRIPTS_PROPERTY . ": " . $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::init
     * init() メソッドが enqueue_block_editor_assets アクションを正しく登録するかテスト
     */
    public function test_init_adds_enqueue_block_editor_assets_action(): void
    {
        // Arrange
        $this->assertFalse(has_action('enqueue_block_editor_assets', [self::TARGET_CLASS, 'enqueue_editor_scripts']));

        // Act
        AuroraDesignBlocksEditorScripts::init();

        // Assert
        // enqueue_block_editor_assets のデフォルト優先度は 10
        $this->assertEquals(10, has_action('enqueue_block_editor_assets', [self::TARGET_CLASS, 'enqueue_editor_scripts']));
    }

    /**
     * @test
     * @covers ::add_scripts
     * 単一のスクリプトを追加できるかテスト
     */
    public function test_add_scripts_adds_single_script(): void
    {
        // Arrange
        $scripts_to_add = ['my-editor-script' => ['path' => '/js/my-editor-script.js', 'deps' => ['wp-blocks']]];

        // Act
        AuroraDesignBlocksEditorScripts::add_scripts($scripts_to_add);

        // Assert
        $added_scripts = $this->get_static_property_value();
        $this->assertEquals($scripts_to_add, $added_scripts);
    }

    /**
     * @test
     * @covers ::add_scripts
     * 複数のスクリプトを追加・追記できるかテスト
     */
    public function test_add_scripts_adds_and_appends_multiple_scripts(): void
    {
        // Arrange: 最初にスクリプトを追加
        $initial_scripts = ['editor-script-1' => ['path' => '/js/editor-script-1.js', 'deps' => []]];
        AuroraDesignBlocksEditorScripts::add_scripts($initial_scripts);

        // Act: さらにスクリプトを追加
        $scripts_to_add = [
            'editor-script-2' => ['path' => '/js/editor-script-2.js', 'deps' => ['wp-i18n']],
            'editor-script-3' => ['path' => '/js/editor-script-3.js', 'deps' => ['wp-element']],
        ];
        AuroraDesignBlocksEditorScripts::add_scripts($scripts_to_add);

        // Assert: 全てのスクリプトがマージされているか確認
        $expected_scripts = array_merge($initial_scripts, $scripts_to_add);
        $added_scripts = $this->get_static_property_value();
        $this->assertEquals($expected_scripts, $added_scripts);
    }

    /**
     * @test
     * @covers ::enqueue_editor_scripts
     * add_scripts で追加されたスクリプトがエンキュー(登録)されるかテスト (シンプル版)
     */
    public function test_enqueue_editor_scripts_enqueues_added_scripts(): void
    {
        // Arrange
        $scripts_to_enqueue = [
            'editor-script-a' => ['path' => '/js/editor-script-a.js', 'deps' => []],
            'editor-script-b' => ['path' => '/js/editor-script-b.js', 'deps' => ['wp-blocks']], // wp-blocks に依存
        ];
        AuroraDesignBlocksEditorScripts::add_scripts($scripts_to_enqueue);
        AuroraDesignBlocksEditorScripts::init(); // フックを登録

        // Act: enqueue_block_editor_assets アクションを実行
        do_action('enqueue_block_editor_assets');

        // Assert: 各スクリプトが登録されたかを確認
        foreach ($scripts_to_enqueue as $handle => $data) {
            $this->assertTrue(wp_script_is($handle, 'registered'), "Script '{$handle}' should be registered.");
            // エディタコンテキストでは 'enqueued' の確認は省略またはオプションとする

            // 依存関係も確認 (オプション)
            global $wp_scripts;
            $registered_script = $wp_scripts->query($handle);
            if ($registered_script && isset($data['deps'])) {
                $this->assertEquals($data['deps'], $registered_script->deps, "Dependencies for script '{$handle}' should be correct.");
            }
        }
    }

    /**
     * @test
     * @covers ::enqueue_editor_scripts
     * スクリプトが追加されていない場合に何もエンキュー(登録)されないかテスト
     */
    public function test_enqueue_editor_scripts_does_nothing_when_no_scripts_added(): void
    {
        // Arrange
        // setUp で静的プロパティとフックはリセット済み
        AuroraDesignBlocksEditorScripts::init(); // フックを登録

        // テスト開始時に登録されていないことを確認
        // AuroraDesignBlocks-functions-block.php で追加される可能性のあるスクリプトをチェック
        $this->assertFalse(wp_script_is('AuroraDesignBlocks-gfontawesome', 'registered'), "Script 'AuroraDesignBlocks-gfontawesome' should not be registered before do_action.");
        $this->assertFalse(wp_script_is('editor-script-a', 'registered'), "Script 'editor-script-a' should not be registered before do_action.");

        // Act
        do_action('enqueue_block_editor_assets');

        // Assert
        global $wp_scripts;
        // 登録されていないことを確認
        $this->assertFalse(wp_script_is('AuroraDesignBlocks-gfontawesome', 'registered'), "Script 'AuroraDesignBlocks-gfontawesome' should not be registered.");
        $this->assertFalse(wp_script_is('editor-script-a', 'registered'), "Script 'editor-script-a' should not be registered.");
        // キューが空であることの確認 (より厳密な場合)
        // $this->assertEmpty($wp_scripts->queue, "Script queue should be empty.");
    }
}
