<?php // tests/unit-tests/AuroraDesignBlocksFrontendStylesTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/AuroraDesignBlocks-functions-outerAssets.php';

// _AuroraDesignBlocks_S_VERSION 定数が定義されていない場合に定義 (テスト環境用)
if (!defined('_AuroraDesignBlocks_S_VERSION')) {
    define('_AuroraDesignBlocks_S_VERSION', '1.0.0');
}

/**
 * AuroraDesignBlocksFrontendStyles クラスのユニットテスト (シンプル版)
 *
 * @coversDefaultClass AuroraDesignBlocksFrontendStyles
 * @group assets
 * @group styles
 */
class aurora_design_functions_outerAssets_FrontendStylesTest extends WP_UnitTestCase // クラス名を修正 (PSR-4推奨) AuroraDesignBlocksFrontendStylesTest
{
    /**
     * テスト対象クラス名
     */
    private const TARGET_CLASS = AuroraDesignBlocksFrontendStyles::class;

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
        // これにより、parent::setUp() 内で after_setup_theme が実行されても
        // AuroraDesignBlocksCommonCssAssets::init が呼び出されるのを防ぐ
        remove_action('after_setup_theme', ['AuroraDesignBlocksCommonCssAssets', 'init']);

        parent::setUp(); // parent::setUp() を後に移動

        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // アクションフックを削除 (wp_enqueue_scripts)
        remove_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'enqueue_styles'], 20);
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
        remove_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'enqueue_styles'], 20);
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
        if (!isset($wp_styles) || !$wp_styles instanceof WP_Styles) {
            $wp_styles = new WP_Styles();
        } else {
            $wp_styles->reset();
        }
        // wp_default_styles($wp_styles); // 必要に応じて
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
     * init() メソッドが wp_enqueue_scripts アクションを正しく登録するかテスト
     */
    public function test_init_adds_wp_enqueue_scripts_action(): void
    {
        // Arrange
        $this->assertFalse(has_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'enqueue_styles']));

        // Act
        AuroraDesignBlocksFrontendStyles::init();

        // Assert
        $this->assertEquals(20, has_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'enqueue_styles']));
    }

    /**
     * @test
     * @covers ::add_styles
     * 単一のスタイルを追加できるかテスト
     */
    public function test_add_styles_adds_single_style(): void
    {
        // Arrange
        $styles_to_add = ['my-style' => '/path/to/my-style.css'];

        // Act
        AuroraDesignBlocksFrontendStyles::add_styles($styles_to_add);

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
        $initial_styles = ['style-1' => '/path/to/style-1.css'];
        AuroraDesignBlocksFrontendStyles::add_styles($initial_styles);

        // Act: さらにスタイルを追加
        $styles_to_add = [
            'style-2' => '/path/to/style-2.css',
            'style-3' => '/path/to/style-3.css',
        ];
        AuroraDesignBlocksFrontendStyles::add_styles($styles_to_add);

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
            'style-a' => '/css/style-a.css',
            'style-b' => '/css/style-b.css',
        ];
        AuroraDesignBlocksFrontendStyles::add_styles($styles_to_enqueue);
        AuroraDesignBlocksFrontendStyles::init(); // フックを登録

        // Act: wp_enqueue_scripts アクションを実行
        do_action('wp_enqueue_scripts');

        // Assert: 各スタイルがエンキューされたか、登録されたかを確認
        foreach ($styles_to_enqueue as $handle => $path) {
            $this->assertTrue(wp_style_is($handle, 'enqueued'), "Style '{$handle}' should be enqueued.");
            // 登録されているかの確認 (オプションだが、エンキューの前提として有用)
            $this->assertTrue(wp_style_is($handle, 'registered'), "Style '{$handle}' should be registered.");
            // 詳細な登録内容 (src, ver, deps) のチェックは省略
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
        AuroraDesignBlocksFrontendStyles::init(); // フックを登録

        // ★★★ 追加: もし 'style-a' が登録されていたら強制的に解除 ★★★
        if (wp_style_is('style-a', 'registered')) {
            wp_deregister_style('style-a');
        }
        // ★★★ 追加: もし 'style-b' が登録されていたら強制的に解除 ★★★
        if (wp_style_is('style-b', 'registered')) {
            wp_deregister_style('style-b');
        }

        // テスト開始時に登録されていないことを確認
        $this->assertFalse(wp_style_is('style-a', 'registered'), "Style 'style-a' should not be registered before do_action (after potential deregister).");
        $this->assertFalse(wp_style_is('style-b', 'registered'), "Style 'style-b' should not be registered before do_action (after potential deregister).");

        // Act
        do_action('wp_enqueue_scripts');

        // Assert
        global $wp_styles;
        // 登録されていないことを確認
        $this->assertFalse(wp_style_is('style-a', 'registered'), "Style 'style-a' should not be registered.");
        $this->assertFalse(wp_style_is('style-b', 'registered'), "Style 'style-b' should not be registered.");
        // キューが空であることの確認 (より厳密な場合)
        // $this->assertEmpty($wp_styles->queue, "Style queue should be empty.");
    }
}
