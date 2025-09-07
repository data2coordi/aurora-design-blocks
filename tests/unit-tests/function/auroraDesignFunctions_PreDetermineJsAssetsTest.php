<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * AuroraDesignBlocksPreDetermineJsAssets クラスのユニットテスト
 *
 * @coversDefaultClass AuroraDesignBlocksPreDetermineJsAssets
 * @group assets
 * @group scripts
 */
class auroraDesignFunctions_PreDetermineJsAssetsTest extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // 各テストの前に静的プロパティをリセット
        $this->reset_static_property(AuroraDesignBlocksFrontendScripts::class, 'scripts');
        $this->reset_static_property(AuroraDesignBlocksDeferJs::class, 'deferred_scripts');
    }

    public function tearDown(): void
    {
        // 各テストの後に静的プロパティをリセット
        $this->reset_static_property(AuroraDesignBlocksFrontendScripts::class, 'scripts');
        $this->reset_static_property(AuroraDesignBlocksDeferJs::class, 'deferred_scripts');
        parent::tearDown();
    }

    private function reset_static_property(string $class, string $property): void
    {
        $reflection = new ReflectionProperty($class, $property);
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);
    }

    /**
     * @test
     * シングル投稿で tab + slider ブロックがある場合
     */
    public function test_init_adds_tab_and_slider_scripts_for_single_post(): void
    {
        $post_id = $this->factory()->post->create([
            'post_content' => '<!-- wp:aurora-design-blocks/tab-block /--><!-- wp:aurora-design-blocks/slider-block /-->'
        ]);
        $this->go_to(get_permalink($post_id));

        // 実行
        AuroraDesignBlocksPreDetermineJsAssets::init();

        // 検証
        $scripts_reflection = new ReflectionProperty(AuroraDesignBlocksFrontendScripts::class, 'scripts');
        $scripts_reflection->setAccessible(true);
        $added_scripts = $scripts_reflection->getValue();

        $this->assertArrayHasKey('aurora-design-blocks-tab-block-script', $added_scripts);
        $this->assertArrayHasKey('aurora-design-blocks-slider-block-script', $added_scripts);
    }

    /**
     * @test
     * シングル投稿で tab のみ
     */
    public function test_init_adds_only_tab_script_for_single_post(): void
    {
        $post_id = $this->factory()->post->create([
            'post_content' => '<!-- wp:aurora-design-blocks/tab-block /-->'
        ]);
        $this->go_to(get_permalink($post_id));

        // 実行
        AuroraDesignBlocksPreDetermineJsAssets::init();

        // 検証
        $scripts_reflection = new ReflectionProperty(AuroraDesignBlocksFrontendScripts::class, 'scripts');
        $scripts_reflection->setAccessible(true);
        $added_scripts = $scripts_reflection->getValue();

        $this->assertArrayHasKey('aurora-design-blocks-tab-block-script', $added_scripts);
        $this->assertArrayNotHasKey('aurora-design-blocks-slider-block-script', $added_scripts);
    }

    /**
     * @test
     * シングル投稿で slider のみ
     */
    public function test_init_adds_only_slider_script_for_single_post(): void
    {
        $post_id = $this->factory()->post->create([
            'post_content' => '<!-- wp:aurora-design-blocks/slider-block /-->'
        ]);
        $this->go_to(get_permalink($post_id));

        // 実行
        AuroraDesignBlocksPreDetermineJsAssets::init();

        // 検証
        $scripts_reflection = new ReflectionProperty(AuroraDesignBlocksFrontendScripts::class, 'scripts');
        $scripts_reflection->setAccessible(true);
        $added_scripts = $scripts_reflection->getValue();

        $this->assertArrayHasKey('aurora-design-blocks-slider-block-script', $added_scripts);
        $this->assertArrayNotHasKey('aurora-design-blocks-tab-block-script', $added_scripts);
    }

    /**
     * @test
     * シングル投稿でブロックなし
     */
    public function test_init_does_not_add_scripts_if_no_blocks_single_post(): void
    {
        $post_id = $this->factory()->post->create(['post_content' => '']);
        $this->go_to(get_permalink($post_id));

        // 実行
        AuroraDesignBlocksPreDetermineJsAssets::init();

        // 検証
        $scripts_reflection = new ReflectionProperty(AuroraDesignBlocksFrontendScripts::class, 'scripts');
        $scripts_reflection->setAccessible(true);
        $added_scripts = $scripts_reflection->getValue();

        $this->assertEmpty($added_scripts);
    }

    /**
     * @test
     * 固定ページでブロックあり
     */
    public function test_init_adds_scripts_for_page_with_blocks(): void
    {
        $page_id = $this->factory()->post->create([
            'post_type' => 'page',
            'post_content' => '<!-- wp:aurora-design-blocks/tab-block /-->'
        ]);
        $this->go_to(get_permalink($page_id));

        // 実行
        AuroraDesignBlocksPreDetermineJsAssets::init();

        // 検証
        $scripts_reflection = new ReflectionProperty(AuroraDesignBlocksFrontendScripts::class, 'scripts');
        $scripts_reflection->setAccessible(true);
        $added_scripts = $scripts_reflection->getValue();

        $this->assertArrayHasKey('aurora-design-blocks-tab-block-script', $added_scripts);
        $this->assertArrayNotHasKey('aurora-design-blocks-slider-block-script', $added_scripts);
    }

    /**
     * @test
     * 非シングルページでは何も追加されない
     */
    public function test_init_does_not_add_scripts_for_non_singular(): void
    {
        $this->go_to(home_url('/'));

        // 実行
        AuroraDesignBlocksPreDetermineJsAssets::init();

        // 検証
        $scripts_reflection = new ReflectionProperty(AuroraDesignBlocksFrontendScripts::class, 'scripts');
        $scripts_reflection->setAccessible(true);
        $added_scripts = $scripts_reflection->getValue();

        $this->assertEmpty($added_scripts);
    }
}
