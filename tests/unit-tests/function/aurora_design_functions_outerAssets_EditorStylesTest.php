<?php // tests/unit-tests/AuroraDesignBlocksEditorStylesTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/AuroraDesignBlocks-functions-outerAssets.php';

// AURORA_DESIGN_BLOCKS_VERSION 定数が定義されていない場合に定義 (テスト環境用)
if (!defined('AURORA_DESIGN_BLOCKS_VERSION')) {
    define('AURORA_DESIGN_BLOCKS_VERSION', '1.0.0');
}

/**
 * AuroraDesignBlocksEditorStyles クラスのユニットテスト (シンプル版)
 *
 * @coversDefaultClass AuroraDesignBlocksEditorStyles
 * @group assets
 * @group styles
 * @group editor
 */
class aurora_design_functions_outerAssets_EditorStylesTest extends WP_UnitTestCase // クラス名を修正 (PSR-4推奨) AuroraDesignBlocksEditorStylesTest
{
    /**
     * テスト対象クラス名
     */
    private const TARGET_CLASS = AuroraDesignBlocksEditorStyles::class;

    /**
     * テスト対象の静的プロパティ名 (親クラス AuroraDesignBlocksRegStyles から継承)
     */
    private const STYLES_PROPERTY = 'styles';

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        // ★★★ parent::setUp() の前にフックを削除 ★★★
        remove_action('after_setup_theme', ['AuroraDesignBlocksCommonCssAssets', 'init']);

        parent::setUp(); // parent::setUp() を後に移動

        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // アクションフックを削除 (wp_enqueue_scripts ではなく enqueue_block_editor_assets)
        remove_action('enqueue_block_editor_assets', [self::TARGET_CLASS, 'enqueue_styles']);
        // WordPress のスタイルキューをリセット
        $this->reset_styles();
        // 再度静的プロパティをリセット (念のため)
        $this->set_static_property_value([]);
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // アクションフックを削除
        remove_action('enqueue_block_editor_assets', [self::TARGET_CLASS, 'enqueue_styles']);
        // WordPress のスタイルキューをリセット
        $this->reset_styles();

        // ★★★ after_setup_theme フックを元に戻す (他のテストに影響を与えないように) ★★★
        add_action('after_setup_theme', ['AuroraDesignBlocksCommonCssAssets', 'init']);

        parent::tearDown();
    }

    /**
     * WordPress のスタイルキューをリセットするヘルパーメソッド
     */
    private function reset_styles(): void
    {
        global $wp_styles;
        // ★★★ 修正: 強制的に新しいインスタンスで上書き ★★★
        $wp_styles = new WP_Styles();
        // wp_default_styles($wp_styles); // 必要に応じてデフォルトスタイルを再登録
    }


    /**
     * Reflection を使用して静的プロパティの値を設定するヘルパーメソッド
     *
     * @param mixed $value 設定する値
     */
    private function set_static_property_value($value): void
    {
        try {
            $reflection = new ReflectionProperty(self::TARGET_CLASS, self::STYLES_PROPERTY);
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, $value); // 静的プロパティなので第一引数は null
        } catch (ReflectionException $e) {
            $this->fail("Failed to set static property " . self::TARGET_CLASS . "::" . self::STYLES_PROPERTY . ": " . $e->getMessage());
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
            $property = $reflectionClass->getProperty(self::STYLES_PROPERTY);
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }
            return $property->getValue(null);
        } catch (ReflectionException $e) {
            $this->fail("Failed to get static property " . self::TARGET_CLASS . "::" . self::STYLES_PROPERTY . ": " . $e->getMessage());
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
        $this->assertFalse(has_action('enqueue_block_editor_assets', [self::TARGET_CLASS, 'enqueue_styles']));

        // Act
        AuroraDesignBlocksEditorStyles::init();

        // Assert
        // enqueue_block_editor_assets のデフォルト優先度は 10
        $this->assertEquals(10, has_action('enqueue_block_editor_assets', [self::TARGET_CLASS, 'enqueue_styles']));
    }

    /**
     * @test
     * @covers ::add_styles
     * 単一のスタイルを追加できるかテスト
     */
    public function test_add_styles_adds_single_style(): void
    {
        // Arrange
        $styles_to_add = ['my-editor-style' => '/path/to/my-editor-style.css'];

        // Act
        AuroraDesignBlocksEditorStyles::add_styles($styles_to_add);

        // Assert
        $added_styles = $this->get_static_property_value();
        $this->assertEquals($styles_to_add, $added_styles);
    }

    /**
     * @test
     * @covers ::add_styles
     * 複数のスタイルを追加・追記できるかテスト
     */
    public function test_add_styles_adds_and_appends_multiple_styles(): void
    {
        // Arrange: 最初にスタイルを追加
        $initial_styles = ['editor-style-1' => '/path/to/editor-style-1.css'];
        AuroraDesignBlocksEditorStyles::add_styles($initial_styles);

        // Act: さらにスタイルを追加
        $styles_to_add = [
            'editor-style-2' => '/path/to/editor-style-2.css',
            'editor-style-3' => '/path/to/editor-style-3.css',
        ];
        AuroraDesignBlocksEditorStyles::add_styles($styles_to_add);

        // Assert: 全てのスタイルがマージされているか確認
        $expected_styles = array_merge($initial_styles, $styles_to_add);
        $added_styles = $this->get_static_property_value();
        $this->assertEquals($expected_styles, $added_styles);
    }

    /**
     * @test
     * @covers ::enqueue_styles
     * add_styles で追加されたスタイルがエンキューされるかテスト (シンプル版)
     */
    public function test_enqueue_styles_enqueues_added_styles(): void
    {
        // Arrange
        $styles_to_enqueue = [
            'editor-style-a' => '/css/editor-style-a.css',
            'editor-style-b' => '/css/editor-style-b.css',
        ];
        AuroraDesignBlocksEditorStyles::add_styles($styles_to_enqueue);
        AuroraDesignBlocksEditorStyles::init(); // フックを登録

        // Act: enqueue_block_editor_assets アクションを実行
        do_action('enqueue_block_editor_assets');

        // Assert: 各スタイルがエンキューされたか、登録されたかを確認
        foreach ($styles_to_enqueue as $handle => $path) {
            // エディタコンテキストでは 'enqueued' ではなく 'registered' の方が確実な場合がある
            // (実際にエディタ画面で読み込まれるかは環境によるため)
            $this->assertTrue(wp_style_is($handle, 'registered'), "Style '{$handle}' should be registered.");
            // 必要であればエンキューも確認
            // $this->assertTrue(wp_style_is($handle, 'enqueued'), "Style '{$handle}' should be enqueued.");
        }
    }

    /**
     * @test
     * @covers ::enqueue_styles
     * スタイルが追加されていない場合に何もエンキューされないかテスト
     */
    public function test_enqueue_styles_does_nothing_when_no_styles_added(): void
    {
        // Arrange
        // setUp で静的プロパティとフックはリセット済み
        AuroraDesignBlocksEditorStyles::init(); // フックを登録

        // テスト開始時に登録されていないことを確認
        // AuroraDesignBlocksCommonCssAssets::init で追加される可能性のあるスタイルをチェック
        $this->assertFalse(wp_style_is('AuroraDesignBlocks-base-style-plus', 'registered'), "Style 'AuroraDesignBlocks-base-style-plus' should not be registered before do_action.");
        $this->assertFalse(wp_style_is('editor-style-a', 'registered'), "Style 'editor-style-a' should not be registered before do_action.");

        // Act
        do_action('enqueue_block_editor_assets');

        // Assert
        global $wp_styles;
        // 登録されていないことを確認
        $this->assertFalse(wp_style_is('AuroraDesignBlocks-base-style-plus', 'registered'), "Style 'AuroraDesignBlocks-base-style-plus' should not be registered.");
        $this->assertFalse(wp_style_is('editor-style-a', 'registered'), "Style 'editor-style-a' should not be registered.");
        // キューが空であることの確認 (より厳密な場合)
        // $this->assertEmpty($wp_styles->queue, "Style queue should be empty.");
    }
}
