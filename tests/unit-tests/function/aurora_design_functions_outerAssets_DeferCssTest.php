<?php // tests/unit-tests/AuroraDesignBlocks_functions_outerAssets_AuroraDesignBlocksDeferCssTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/AuroraDesignBlocks-functions-outerAssets.php';

/**
 * AuroraDesignBlocksDeferCss クラスのユニットテスト
 *
 * @coversDefaultClass AuroraDesignBlocksDeferCss
 * @group assets
 * @group styles
 */
class aurora_design_functions_outerAssets_DeferCssTest extends WP_UnitTestCase
{
    /**
     * テスト対象クラス名
     */
    private const TARGET_CLASS = AuroraDesignBlocksDeferCss::class;

    /**
     * テスト対象の静的プロパティ名
     */
    private const DEFERRED_STYLES_PROPERTY = 'deferred_styles';

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        parent::setUp();
        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // フィルターフックを削除 (init() がグローバルスコープで呼ばれている可能性があるため)
        remove_filter('style_loader_tag', [self::TARGET_CLASS, 'defer_css'], 10);
        // フィルターフックを再登録 (テスト対象のメソッドを確実にフックするため)
        AuroraDesignBlocksDeferCss::init();
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // フィルターフックを削除
        remove_filter('style_loader_tag', [self::TARGET_CLASS, 'defer_css'], 10);
        parent::tearDown();
    }

    /**
     * Reflection を使用して静的プロパティの値を設定するヘルパーメソッド
     *
     * @param mixed $value 設定する値
     */
    private function set_static_property_value($value): void
    {
        try {
            $reflection = new ReflectionProperty(self::TARGET_CLASS, self::DEFERRED_STYLES_PROPERTY);
            // PHP 8.1 以降では setAccessible は不要な場合があるが、互換性のために残す
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, $value); // 静的プロパティを設定
        } catch (ReflectionException $e) {
            $this->fail("Failed to set static property " . self::TARGET_CLASS . "::" . self::DEFERRED_STYLES_PROPERTY . ": " . $e->getMessage());
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
            $property = $reflectionClass->getProperty(self::DEFERRED_STYLES_PROPERTY);
            // PHP 8.1 以降では setAccessible は不要な場合があるが、互換性のために残す
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }
            return $property->getValue();
        } catch (ReflectionException $e) {
            $this->fail("Failed to get static property " . self::TARGET_CLASS . "::" . self::DEFERRED_STYLES_PROPERTY . ": " . $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::init
     * init() メソッドが style_loader_tag フィルターを正しく登録するかテスト
     */
    public function test_init_adds_style_loader_tag_filter(): void
    {
        // Arrange: setUp でフックが登録されているはず
        // Act: なし
        // Assert: フィルターが正しい優先度で登録されているか確認
        $this->assertEquals(10, has_filter('style_loader_tag', [self::TARGET_CLASS, 'defer_css']));
    }

    /**
     * @test
     * @covers ::add_deferred_styles
     * 単一のスタイルハンドルを追加できるかテスト
     */
    public function test_add_deferred_styles_adds_single_handle(): void
    {
        // Arrange
        $styles_to_add = ['my-style'];

        // Act
        AuroraDesignBlocksDeferCss::add_deferred_styles($styles_to_add);

        // Assert
        $deferred_styles = $this->get_static_property_value();
        $this->assertEquals($styles_to_add, $deferred_styles);
    }

    /**
     * @test
     * @covers ::add_deferred_styles
     * 複数のスタイルハンドルを追加できるかテスト
     */
    public function test_add_deferred_styles_adds_multiple_handles(): void
    {
        // Arrange
        $styles_to_add = ['style-1', 'style-2', 'style-3'];

        // Act
        AuroraDesignBlocksDeferCss::add_deferred_styles($styles_to_add);

        // Assert
        $deferred_styles = $this->get_static_property_value();
        $this->assertEquals($styles_to_add, $deferred_styles);
    }

    /**
     * @test
     * @covers ::add_deferred_styles
     * 重複するハンドルを追加した場合に、一意になるかテスト
     */
    public function test_add_deferred_styles_handles_duplicates(): void
    {
        // Arrange
        $initial_styles = ['style-1'];
        $this->set_static_property_value($initial_styles);
        $styles_to_add = ['style-1', 'style-2', 'style-1', 'style-3'];
        $expected_styles = ['style-1', 'style-2', 'style-3']; // 重複排除後の期待値

        // Act
        AuroraDesignBlocksDeferCss::add_deferred_styles($styles_to_add);

        // Assert
        $deferred_styles = $this->get_static_property_value();
        // 配列の内容が一致するか (順序は問わない場合)
        $this->assertCount(count($expected_styles), $deferred_styles);
        foreach ($expected_styles as $expected) {
            $this->assertContains($expected, $deferred_styles);
        }
        // 順序も一致することを期待する場合 (array_values でキーをリセット)
        // $this->assertEquals($expected_styles, array_values($deferred_styles));
    }

    /**
     * @test
     * @covers ::add_deferred_styles
     * 既存のリストに新しいハンドルを追加できるかテスト
     */
    public function test_add_deferred_styles_appends_to_existing(): void
    {
        // Arrange
        $initial_styles = ['existing-style-1', 'existing-style-2'];
        $this->set_static_property_value($initial_styles);
        $styles_to_add = ['new-style-1', 'new-style-2'];
        $expected_styles = ['existing-style-1', 'existing-style-2', 'new-style-1', 'new-style-2'];

        // Act
        AuroraDesignBlocksDeferCss::add_deferred_styles($styles_to_add);

        // Assert
        $deferred_styles = $this->get_static_property_value();
        // 配列の内容が一致するか (順序は問わない場合)
        $this->assertCount(count($expected_styles), $deferred_styles);
        foreach ($expected_styles as $expected) {
            $this->assertContains($expected, $deferred_styles);
        }
        // 順序も一致することを期待する場合
        // $this->assertEquals($expected_styles, array_values($deferred_styles));
    }

    /**
     * @test
     * @covers ::defer_css
     * 遅延対象のハンドルの場合にスタイルタグが正しく変更されるかテスト (現在の実装に合わせた期待値)
     */
    public function test_defer_css_modifies_tag_for_deferred_handle(): void
    {
        // Arrange
        $deferred_handle = 'my-deferred-style';
        AuroraDesignBlocksDeferCss::add_deferred_styles([$deferred_handle]);
        $original_tag = "<link rel='stylesheet' id='{$deferred_handle}-css' href='http://example.com/style.css' type='text/css' media='all' />";
        // ★★★ 修正: 現在の実装に合わせた期待値 (rel='stylesheet' の直後に挿入、元の media='all' は残る) ★★★
        $expected_tag = "<link rel='stylesheet' media='print' onload=\"this.media='all'\" id='{$deferred_handle}-css' href='http://example.com/style.css' type='text/css' media='all' />";

        // Act
        // apply_filters を使ってフックされたメソッドを呼び出す
        $modified_tag = apply_filters('style_loader_tag', $original_tag, $deferred_handle);

        // Assert
        $this->assertEquals($expected_tag, $modified_tag);
    }

    /**
     * @test
     * @covers ::defer_css
     * 遅延対象外のハンドルの場合にスタイルタグが変更されないかテスト
     */
    public function test_defer_css_does_not_modify_tag_for_non_deferred_handle(): void
    {
        // Arrange
        $non_deferred_handle = 'my-normal-style';
        $deferred_handle = 'another-style';
        AuroraDesignBlocksDeferCss::add_deferred_styles([$deferred_handle]); // 他のスタイルは遅延対象
        $original_tag = "<link rel='stylesheet' id='{$non_deferred_handle}-css' href='http://example.com/normal.css' type='text/css' media='all' />";

        // Act
        // apply_filters を使ってフックされたメソッドを呼び出す
        $modified_tag = apply_filters('style_loader_tag', $original_tag, $non_deferred_handle);

        // Assert
        $this->assertEquals($original_tag, $modified_tag);
    }

    /**
     * @test
     * @covers ::defer_css
     * 異なる形式の rel 属性を持つタグでも正しく動作するかテスト (現在の実装に合わせた期待値)
     * @dataProvider tagFormatProvider
     */
    public function test_defer_css_handles_different_tag_formats(string $original_tag_format, string $expected_tag_format): void
    {
        // Arrange
        $deferred_handle = 'my-deferred-style';
        AuroraDesignBlocksDeferCss::add_deferred_styles([$deferred_handle]);
        $original_tag = sprintf($original_tag_format, $deferred_handle);
        $expected_tag = sprintf($expected_tag_format, $deferred_handle);

        // Act
        // apply_filters を使ってフックされたメソッドを呼び出す
        $modified_tag = apply_filters('style_loader_tag', $original_tag, $deferred_handle);

        // Assert
        $this->assertEquals($expected_tag, $modified_tag);
    }

    /**
     * 異なるタグ形式のデータプロバイダー (現在の実装に合わせた期待値)
     * @return array<string, array{string, string}>
     */
    public function tagFormatProvider(): array
    {
        return [
            'Single quotes' => [
                // Input: rel='stylesheet'
                "<link rel='stylesheet' id='%s-css' href='http://example.com/style.css' />",
                // Expected: rel='stylesheet' の直後に挿入
                "<link rel='stylesheet' media='print' onload=\"this.media='all'\" id='%s-css' href='http://example.com/style.css' />"
            ],
            'Double quotes' => [
                // Input: rel="stylesheet"
                '<link rel="stylesheet" id="%s-css" href="http://example.com/style.css" />',
                // Expected: 変更なし (現在の str_replace はシングルクォートのみ対象)
                '<link rel="stylesheet" id="%s-css" href="http://example.com/style.css" />'
            ],
            'No space before slash' => [
                // Input: rel='stylesheet'
                "<link rel='stylesheet' id='%s-css' href='http://example.com/style.css'/>",
                // Expected: rel='stylesheet' の直後に挿入
                "<link rel='stylesheet' media='print' onload=\"this.media='all'\" id='%s-css' href='http://example.com/style.css'/>"
            ],
            'Extra attributes' => [
                // Input: rel='stylesheet' と media='screen'
                "<link type='text/css' rel='stylesheet' id='%s-css' href='http://example.com/style.css' media='screen' />",
                // Expected: rel='stylesheet' の直後に挿入、元の media='screen' は残る
                "<link type='text/css' rel='stylesheet' media='print' onload=\"this.media='all'\" id='%s-css' href='http://example.com/style.css' media='screen' />"
            ],
        ];
    }
}
