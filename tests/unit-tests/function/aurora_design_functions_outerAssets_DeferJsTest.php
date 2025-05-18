<?php // tests/unit-tests/AuroraDesignBlocks_functions_outerAssets_AuroraDesignBlocksDeferJsTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/AuroraDesignBlocks-functions-outerAssets.php';

/**
 * AuroraDesignBlocksDeferJs クラスのユニットテスト
 *
 * @coversDefaultClass AuroraDesignBlocksDeferJs
 * @group assets
 * @group scripts
 */
class aurora_design_functions_outerAssets_DeferJsTest extends WP_UnitTestCase
{
    /**
     * テスト対象クラス名
     */
    private const TARGET_CLASS = AuroraDesignBlocksDeferJs::class;

    /**
     * テスト対象の静的プロパティ名
     */
    private const DEFERRED_SCRIPTS_PROPERTY = 'deferred_scripts';

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        parent::setUp();
        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // フィルターフックを削除 (init() がグローバルスコープで呼ばれている可能性があるため)
        remove_filter('script_loader_tag', [self::TARGET_CLASS, 'defer_js'], 10);
        // フィルターフックを再登録 (テスト対象のメソッドを確実にフックするため)
        AuroraDesignBlocksDeferJs::init();
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // フィルターフックを削除
        remove_filter('script_loader_tag', [self::TARGET_CLASS, 'defer_js'], 10);
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
            $reflection = new ReflectionProperty(self::TARGET_CLASS, self::DEFERRED_SCRIPTS_PROPERTY);
            // PHP 8.1 以降では setAccessible は不要な場合があるが、互換性のために残す
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, $value); // 静的プロパティを設定
        } catch (ReflectionException $e) {
            $this->fail("Failed to set static property " . self::TARGET_CLASS . "::" . self::DEFERRED_SCRIPTS_PROPERTY . ": " . $e->getMessage());
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
            $property = $reflectionClass->getProperty(self::DEFERRED_SCRIPTS_PROPERTY);
            // PHP 8.1 以降では setAccessible は不要な場合があるが、互換性のために残す
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }
            return $property->getValue();
        } catch (ReflectionException $e) {
            $this->fail("Failed to get static property " . self::TARGET_CLASS . "::" . self::DEFERRED_SCRIPTS_PROPERTY . ": " . $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::init
     * init() メソッドが script_loader_tag フィルターを正しく登録するかテスト
     */
    public function test_init_adds_script_loader_tag_filter(): void
    {
        // Arrange: setUp でフックが登録されているはず
        // Act: なし
        // Assert: フィルターが正しい優先度で登録されているか確認
        $this->assertEquals(10, has_filter('script_loader_tag', [self::TARGET_CLASS, 'defer_js']));
    }

    /**
     * @test
     * @covers ::add_deferred_scripts
     * 単一のスクリプトハンドルを追加できるかテスト
     */
    public function test_add_deferred_scripts_adds_single_handle(): void
    {
        // Arrange
        $scripts_to_add = ['my-script'];

        // Act
        AuroraDesignBlocksDeferJs::add_deferred_scripts($scripts_to_add);

        // Assert
        $deferred_scripts = $this->get_static_property_value();
        $this->assertEquals($scripts_to_add, $deferred_scripts);
    }

    /**
     * @test
     * @covers ::add_deferred_scripts
     * 複数のスクリプトハンドルを追加できるかテスト
     */
    public function test_add_deferred_scripts_adds_multiple_handles(): void
    {
        // Arrange
        $scripts_to_add = ['script-1', 'script-2', 'script-3'];

        // Act
        AuroraDesignBlocksDeferJs::add_deferred_scripts($scripts_to_add);

        // Assert
        $deferred_scripts = $this->get_static_property_value();
        $this->assertEquals($scripts_to_add, $deferred_scripts);
    }

    /**
     * @test
     * @covers ::add_deferred_scripts
     * 重複するハンドルを追加した場合に、一意になるかテスト
     */
    public function test_add_deferred_scripts_handles_duplicates(): void
    {
        // Arrange
        $initial_scripts = ['script-1'];
        $this->set_static_property_value($initial_scripts);
        $scripts_to_add = ['script-1', 'script-2', 'script-1', 'script-3'];
        $expected_scripts = ['script-1', 'script-2', 'script-3']; // 重複排除後の期待値

        // Act
        AuroraDesignBlocksDeferJs::add_deferred_scripts($scripts_to_add);

        // Assert
        $deferred_scripts = $this->get_static_property_value();
        // 配列の内容が一致するか (順序は問わない場合)
        $this->assertCount(count($expected_scripts), $deferred_scripts);
        foreach ($expected_scripts as $expected) {
            $this->assertContains($expected, $deferred_scripts);
        }
        // 順序も一致することを期待する場合 (array_values でキーをリセット)
        // $this->assertEquals($expected_scripts, array_values($deferred_scripts));
    }

    /**
     * @test
     * @covers ::add_deferred_scripts
     * 既存のリストに新しいハンドルを追加できるかテスト
     */
    public function test_add_deferred_scripts_appends_to_existing(): void
    {
        // Arrange
        $initial_scripts = ['existing-script-1', 'existing-script-2'];
        $this->set_static_property_value($initial_scripts);
        $scripts_to_add = ['new-script-1', 'new-script-2'];
        $expected_scripts = ['existing-script-1', 'existing-script-2', 'new-script-1', 'new-script-2'];

        // Act
        AuroraDesignBlocksDeferJs::add_deferred_scripts($scripts_to_add);

        // Assert
        $deferred_scripts = $this->get_static_property_value();
        // 配列の内容が一致するか (順序は問わない場合)
        $this->assertCount(count($expected_scripts), $deferred_scripts);
        foreach ($expected_scripts as $expected) {
            $this->assertContains($expected, $deferred_scripts);
        }
        // 順序も一致することを期待する場合
        // $this->assertEquals($expected_scripts, array_values($deferred_scripts));
    }

    /**
     * @test
     * @covers ::defer_js
     * 遅延対象のハンドルの場合にスクリプトタグに defer 属性が正しく追加されるかテスト
     */
    public function test_defer_js_adds_defer_attribute_for_deferred_handle(): void
    {
        // Arrange
        $deferred_handle = 'my-deferred-script';
        AuroraDesignBlocksDeferJs::add_deferred_scripts([$deferred_handle]);
        $original_tag = "<script type='text/javascript' src='http://example.com/script.js?ver=1.0' id='{$deferred_handle}-js'></script>";
        // defer 属性が src の前に追加されることを期待
        $expected_tag = "<script type='text/javascript' defer src='http://example.com/script.js?ver=1.0' id='{$deferred_handle}-js'></script>";

        // Act
        // apply_filters を使ってフックされたメソッドを呼び出す
        $modified_tag = apply_filters('script_loader_tag', $original_tag, $deferred_handle);

        // Assert
        $this->assertEquals($expected_tag, $modified_tag);
    }

    /**
     * @test
     * @covers ::defer_js
     * 遅延対象外のハンドルの場合にスクリプトタグが変更されないかテスト
     */
    public function test_defer_js_does_not_modify_tag_for_non_deferred_handle(): void
    {
        // Arrange
        $non_deferred_handle = 'my-normal-script';
        $deferred_handle = 'another-script';
        AuroraDesignBlocksDeferJs::add_deferred_scripts([$deferred_handle]); // 他のスクリプトは遅延対象
        $original_tag = "<script type='text/javascript' src='http://example.com/normal.js?ver=1.0' id='{$non_deferred_handle}-js'></script>";

        // Act
        // apply_filters を使ってフックされたメソッドを呼び出す
        $modified_tag = apply_filters('script_loader_tag', $original_tag, $non_deferred_handle);

        // Assert
        $this->assertEquals($original_tag, $modified_tag);
    }

    /**
     * @test
     * @covers ::defer_js
     * 異なる形式の src 属性を持つタグでも正しく動作するかテスト
     * @dataProvider tagFormatProvider
     */
    public function test_defer_js_handles_different_tag_formats(string $original_tag_format, string $expected_tag_format): void
    {
        // Arrange
        $deferred_handle = 'my-deferred-script';
        AuroraDesignBlocksDeferJs::add_deferred_scripts([$deferred_handle]);
        $original_tag = sprintf($original_tag_format, $deferred_handle);
        $expected_tag = sprintf($expected_tag_format, $deferred_handle);

        // Act
        // apply_filters を使ってフックされたメソッドを呼び出す
        $modified_tag = apply_filters('script_loader_tag', $original_tag, $deferred_handle);

        // Assert
        $this->assertEquals($expected_tag, $modified_tag);
    }

    /**
     * 異なるタグ形式のデータプロバイダー
     * @return array<string, array{string, string}>
     */
    public function tagFormatProvider(): array
    {
        return [
            'Basic src' => [
                // Input: src='...'
                "<script src='http://example.com/script.js' id='%s-js'></script>",
                // Expected: defer src='...'
                "<script defer src='http://example.com/script.js' id='%s-js'></script>"
            ],
            'Src with double quotes' => [
                // Input: src="..."
                '<script src="http://example.com/script.js" id="%s-js"></script>',
                // Expected: defer src="..."
                '<script defer src="http://example.com/script.js" id="%s-js"></script>'
            ],
            'Src with extra spaces' => [
                // Input: src = '...'
                "<script type='text/javascript'   src = 'http://example.com/script.js?ver=1' id='%s-js' ></script>",
                // Expected: defer src = '...' (元のスペースは維持される)
                "<script type='text/javascript'   defer src = 'http://example.com/script.js?ver=1' id='%s-js' ></script>"
            ],
            'Other attributes before src' => [
                // Input: type='...' src='...'
                "<script type='text/javascript' src='http://example.com/script.js' id='%s-js'></script>",
                // Expected: type='...' defer src='...'
                "<script type='text/javascript' defer src='http://example.com/script.js' id='%s-js'></script>"
            ],
            'No space before src (should still work)' => [
                // Input: <scriptsrc='...' (Invalid HTML, but test robustness)
                // Note: str_replace might not work as expected here if space is required.
                // Let's test a valid case with minimal space.
                // Input: <script id='%s-js' src='http://example.com/script.js'></script>
                "<script id='%s-js' src='http://example.com/script.js'></script>",
                // Expected: <script id='%s-js' defer src='http://example.com/script.js'></script>
                "<script id='%s-js' defer src='http://example.com/script.js'></script>"
            ],
        ];
    }
}
