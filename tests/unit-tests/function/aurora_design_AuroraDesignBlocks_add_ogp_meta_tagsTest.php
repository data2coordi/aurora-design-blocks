<?php

/**
 * Class aurora_designTest
 *
 * @package Aurora_Design_Blocks
 */

/**
 * Minimal test case specifically for OGP meta tags functionality
 * from inc/aurora-design.php.
 */
class aurora_design_AuroraDesignBlocks_add_ogp_meta_tagsTest extends WP_UnitTestCase
{
    /**
     * 各テストの前にテスト環境をセットアップします。
     */
    public function setUp(): void
    {
        parent::setUp();
        // inc/aurora-design.php を直接ロードします。
        // このファイルがプラグインルートからの相対パスで他のファイルを必要とする場合、
        // またはプラグインの定数に依存する場合は、それらもここで定義/ロードする必要があります。
        // AURORA_DESIGN_BLOCKS_PATH は inc/aurora-design.php 内では直接使用されていませんが、
        // 念のため定義しておきます。
        if (!defined('AURORA_DESIGN_BLOCKS_PATH')) {
            // このパスは、テストファイルの位置からプラグインのルートディレクトリを指すように調整してください。
            // 現在のファイル構造 (tests/unit-tests/aurora_designTest.php) を前提としています。
            define('AURORA_DESIGN_BLOCKS_PATH', dirname(__DIR__, 2) . '/');
        }
        require_once AURORA_DESIGN_BLOCKS_PATH . 'inc/aurora-design-blocks.php';

        // add_ogp_meta_tags が wp_head にフックされていることを確認し、
        // もしフックされていなければテスト内でフックします。
        // inc/aurora-design.php を直接ロードした場合、ファイル読み込み時に
        // add_action が実行されるはずですが、念のため。
        if (!has_action('wp_head', 'AuroraDesignBlocks_add_ogp_meta_tags')) {
            add_action('wp_head', 'AuroraDesignBlocks_add_ogp_meta_tags');
        }
    }

    /**
     * Tests basic OGP meta tags output on a singular post.
     * This focuses on title and type as a minimal check.
     */
    public function test_minimal_ogp_output_on_singular_post()
    {
        $post_title = 'Minimal OGP Test Title';
        $post_id = self::factory()->post->create([
            'post_title'   => $post_title,
            'post_content' => 'Minimal content for OGP test.',
        ]);

        $this->go_to(get_permalink($post_id));
        $this->assertTrue(is_singular(), 'Test setup: Should be on a singular page.');

        ob_start();
        // add_ogp_meta_tags は wp_head にフックされているので、do_action で実行
        do_action('wp_head');
        $output = ob_get_clean();

        $expected_title = esc_attr($post_title);

        // 最も基本的なOGPタグ（タイトルとタイプ）が存在するかを確認
        $this->assertStringContainsString("<meta property=\"og:title\" content=\"{$expected_title}\" />", $output, "OGP title tag not found or incorrect.");
        $this->assertStringContainsString("<meta property=\"og:type\" content=\"website\" />", $output, "OGP type tag not found or incorrect.");

        wp_delete_post($post_id, true);
    }
}
