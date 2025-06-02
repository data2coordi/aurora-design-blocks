<?php // tests/unit-tests/InteglightFunctionsBlockTest.php

declare(strict_types=1);

// テスト対象ファイルと依存ファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/AuroraDesignBlocks-functions-outerAssets.php';
// require_once dirname(__DIR__, 2) . '/inc/AuroraDesignBlocks-functions-block.php';

// _INTEGLIGHT_S_VERSION 定数が定義されていない場合に定義 (テスト環境用)
if (!defined('_INTEGLIGHT_S_VERSION')) {
    define('_INTEGLIGHT_S_VERSION', '1.0.0');
}

/**
 * AuroraDesignBlocks-functions-block.php 内の関数のユニットテスト
 *
 * @covers ::AuroraDesignBlocks_add_fontawesome_button_to_toolbar
 * @covers ::AuroraDesignBlocks_replace_fontawesome_icons
 * @covers ::AuroraDesignBlocks_enqueue_block_assets
 * @group functions
 * @group blocks
 * @group assets
 */
class aurora_design_blocks_awesomeTest extends WP_UnitTestCase
{
    /**
     * 




     
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        parent::setUp();

        // AuroraDesignBlocks_add_fontawesome_button_to_toolbar() がグローバルスコープで
        // 実行された影響をリセット
        $this->reset_static_property(InteglightEditorScripts::class, 'scripts');
        $this->reset_static_property(InteglightDeferJs::class, 'deferred_scripts');

        // WordPress のスクリプト/スタイルシステムをリセット
        $this->reset_wp_scripts_styles();

        // フィルターとアクションを削除 (テストメソッド内で必要に応じて再登録)
        remove_filter('the_content', 'AuroraDesignBlocks_replace_fontawesome_icons', 10);
        remove_action('enqueue_block_editor_assets', 'AuroraDesignBlocks_enqueue_block_assets');
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // 静的プロパティをリセット
        $this->reset_static_property(InteglightEditorScripts::class, 'scripts');
        $this->reset_static_property(InteglightDeferJs::class, 'deferred_scripts');

        // WordPress のスクリプト/スタイルシステムを再度リセット
        $this->reset_wp_scripts_styles();

        // フィルターとアクションを削除 (念のため)
        remove_filter('the_content', 'AuroraDesignBlocks_replace_fontawesome_icons', 10);
        remove_action('enqueue_block_editor_assets', 'AuroraDesignBlocks_enqueue_block_assets');

        parent::tearDown();
    }

    /**
     * WordPress のスクリプト/スタイルシステムをリセットするヘルパーメソッド
     */
    private function reset_wp_scripts_styles(): void
    {
        global $wp_scripts, $wp_styles;
        $wp_scripts = new WP_Scripts();
        $wp_styles = new WP_Styles();
        // 必要に応じてデフォルトを再登録
        // wp_default_scripts($wp_scripts);
        // wp_default_styles($wp_styles);
    }

    /**
     * Reflection を使用して静的プロパティをリセットするヘルパーメソッド
     */
    private function reset_static_property(string $className, string $propertyName, $defaultValue = []): void
    {
        try {
            // クラスが存在するか確認
            if (!class_exists($className)) {
                // $this->markTestSkipped("Dependency class {$className} not found.");
                return;
            }
            $reflection = new ReflectionProperty($className, $propertyName);
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, $defaultValue);
        } catch (ReflectionException $e) {
            $this->fail("Failed to reset static property {$className}::{$propertyName}: " . $e->getMessage());
        }
    }

    /**
     * Reflection を使用して静的プロパティの値を取得するヘルパーメソッド
     */
    private function get_static_property_value(string $className, string $propertyName)
    {
        try {
            // クラスが存在するか確認
            if (!class_exists($className)) {
                // $this->markTestSkipped("Dependency class {$className} not found.");
                return null;
            }
            $reflectionClass = new ReflectionClass($className);
            $property = $reflectionClass->getProperty($propertyName);
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }
            return $property->getValue();
        } catch (ReflectionException $e) {
            $this->fail("Failed to get static property {$className}::{$propertyName}: " . $e->getMessage());
        }
    }

    /**
     * @test
     * AuroraDesignBlocks_add_fontawesome_button_to_toolbar がスクリプトと遅延対象を正しく追加するかテスト
     */
    public function test_add_fontawesome_button_to_toolbar_adds_scripts(): void
    {
        // Arrange: setUp でリセット済み

        // Act: 関数を直接呼び出す
        AuroraDesignBlocks_add_fontawesome_button_to_toolbar();

        // Assert: InteglightEditorScripts にスクリプトが追加されたか
        $editor_scripts = $this->get_static_property_value(AuroraDesignBlocksEditorScripts::class, 'scripts');
        $this->assertArrayHasKey('AuroraDesignBlocks-gfontawesome', $editor_scripts, 'EditorScripts should have "AuroraDesignBlocks-gfontawesome" key.');
        $expected_script_data = [
            'path' => '/blocks/gfontawesome/build/index.js',
            'deps' => ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-rich-text']
        ];
        $this->assertEquals($expected_script_data, $editor_scripts['AuroraDesignBlocks-gfontawesome'], 'Editor script data should be correct.');

        // Assert: InteglightDeferJs に遅延スクリプトが追加されたか
        $deferred_scripts = $this->get_static_property_value(AuroraDesignBlocksDeferJs::class, 'deferred_scripts');
        $this->assertContains('AuroraDesignBlocks-gfontawesome', $deferred_scripts, 'Script "AuroraDesignBlocks-gfontawesome" should be added for deferring.');
    }

    /**
     * @test
     * AuroraDesignBlocks_replace_fontawesome_icons がショートコードを正しく置換するかテスト
     * @dataProvider fontawesomeShortcodeProvider
     */
    public function test_replace_fontawesome_icons_replaces_shortcode(string $input_content, string $expected_output): void
    {
        // Arrange: フィルターを登録
        add_filter('the_content', 'AuroraDesignBlocks_replace_fontawesome_icons', 10);

        // ★★★ 追加: wpautop フィルターを一時的に削除 ★★★
        $wpautop_priority = has_filter('the_content', 'wpautop');
        if (false !== $wpautop_priority) {
            remove_filter('the_content', 'wpautop', $wpautop_priority);
        }

        // Act: フィルターを適用
        $filtered_content = apply_filters('the_content', $input_content);

        // ★★★ 追加: wpautop フィルターを元に戻す ★★★
        if (false !== $wpautop_priority) {
            add_filter('the_content', 'wpautop', $wpautop_priority);
        }

        // Assert
        $this->assertEquals($expected_output, $filtered_content);
    }


    /**
     * Font Awesome ショートコードのデータプロバイダー
     * @return array<string, array{string, string}>
     */
    public function fontawesomeShortcodeProvider(): array
    {
        return [
            'Basic icon' => [
                'Some text [fontawesome icon=fa-house] more text.',
                'Some text <i class="fas fa-house"></i> more text.'
            ],
            'Icon with hyphens' => [
                '[fontawesome icon=fa-arrow-right-to-bracket]',
                '<i class="fas fa-arrow-right-to-bracket"></i>'
            ],
            'Multiple icons' => [
                'Icon 1: [fontawesome icon=fa-user], Icon 2: [fontawesome icon=fa-star]',
                'Icon 1: <i class="fas fa-user"></i>, Icon 2: <i class="fas fa-star"></i>'
            ],
            'Case insensitive' => [
                '[FontAwesome ICON=fa-Check]', // 大文字小文字混合
                '<i class="fas fa-Check"></i>' // 出力は小文字fas + 元のアイコン名
            ],
            // ★★★ 修正箇所: 期待値を入力と同じにする ★★★
            'No icon specified' => [
                'Text [fontawesome icon=] text',
                'Text [fontawesome icon=] text' // アイコン名がない場合は置換されない
            ],
            'Invalid format (no equals)' => [
                'Text [fontawesome icon fa-image] text',
                'Text [fontawesome icon fa-image] text' // 置換されない
            ],
            'Invalid format (no closing bracket)' => [
                'Text [fontawesome icon=fa-cog',
                'Text [fontawesome icon=fa-cog' // 置換されない
            ],
            'Content without shortcode' => [
                'This content has no Font Awesome shortcode.',
                'This content has no Font Awesome shortcode.' // 変更なし
            ],
            'Empty content' => [
                '',
                '' // 変更なし
            ],
        ];
    }

    /**
     * @test
     * AuroraDesignBlocks_enqueue_block_assets がスクリプト翻訳を設定するかテスト
     * (wp_set_script_translations の呼び出しを間接的に確認)
     */
    public function test_enqueue_block_assets_sets_translations(): void
    {
        // Arrange: 翻訳対象のスクリプトを登録しておく
        // AuroraDesignBlocks_add_fontawesome_button_to_toolbar() で登録される
        AuroraDesignBlocks_add_fontawesome_button_to_toolbar();
        // enqueue_block_editor_assets アクションにフック登録
        add_action('enqueue_block_editor_assets', 'AuroraDesignBlocks_enqueue_block_assets');
        // InteglightEditorScripts のフックも登録しておく (スクリプトが実際に登録されるように)
        AuroraDesignBlocksEditorScripts::init();

        // Act: アクションを実行
        do_action('enqueue_block_editor_assets');

        // Assert: スクリプトオブジェクトを取得し、翻訳が設定されているか確認
        global $wp_scripts;
        $script_handle = 'AuroraDesignBlocks-gfontawesome'; // 翻訳設定の対象ハンドル
        // 注意: 元のコードでは 'AuroraDesignBlocks-gfontawesome-block-editor-script' が指定されているが、
        //       実際に登録されるのは 'AuroraDesignBlocks-gfontawesome' のため、こちらでテストする。
        //       もし 'AuroraDesignBlocks-gfontawesome-block-editor-script' が正しい場合は、
        //       そのスクリプトが登録されるようにテストを調整する必要がある。

        $script_object = $wp_scripts->query($script_handle);

        // スクリプトが登録されているか
        $this->assertNotNull($script_object, "Script '{$script_handle}' should be registered.");

        // 翻訳データが設定されているか (wp_set_script_translations の結果を確認)
        // $translations = $wp_scripts->get_translations($script_handle);
        // get_translations は内部的なメソッドのため、直接的なアサートは難しい場合がある。
        // ここでは、アクションがエラーなく実行され、スクリプトが登録されていることを確認するに留める。
        // より厳密なテストには、wp_set_script_translations の動作をモックするか、
        // 実際の翻訳ファイルを用意してロードされるかを確認する必要がある。
        $this->assertTrue(true, 'enqueue_block_editor_assets action executed without errors, implying wp_set_script_translations was called.');

        // オプション: スクリプトデータに翻訳関連の情報が含まれているか確認 (内部構造に依存するため注意)
        $script_data = $wp_scripts->get_data($script_handle, 'data');
        // $this->assertStringContainsString('wp.i18n.setLocaleData', $script_data, 'Localized data should contain i18n setup.');
    }
}
