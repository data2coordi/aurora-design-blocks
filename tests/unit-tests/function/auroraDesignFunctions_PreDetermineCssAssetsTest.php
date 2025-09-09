<?php

declare(strict_types=1);

/**
 * AuroraDesignBlocksPreDetermineCssAssets クラスのユニットテスト
 *
 * @coversDefaultClass AuroraDesignBlocksPreDetermineCssAssets
 * @group assets
 * @group styles
 */
class auroraDesignFunctions_PreDetermineCssAssetsTest extends WP_UnitTestCase
{
    private const TARGET_CLASS = AuroraDesignBlocksPreDetermineCssAssets::class;

    private const STYLES_PROPERTY       = 'styles';
    private const EDITOR_STYLES_PROPERTY = 'EditorStyles';
    private const DEFERRED_STYLES_PROPERTY = 'deferredStyles';

    public function setUp(): void
    {
        parent::setUp();
        $this->reset_static_property(self::STYLES_PROPERTY, []);
        $this->reset_static_property(self::EDITOR_STYLES_PROPERTY, []);
    }

    public function tearDown(): void
    {
        $this->reset_static_property(self::STYLES_PROPERTY, []);
        $this->reset_static_property(self::EDITOR_STYLES_PROPERTY, []);
        parent::tearDown();
    }

    /**
     * @test
     * @covers ::init
     * シングル投稿ページでスタイルが追加されるか
     */
    public function test_init_adds_styles_for_single(): void
    {
        // 投稿を作成してそのページにアクセス
        $post_id = $this->factory()->post->create();
        $this->go_to(get_permalink($post_id));

        self::TARGET_CLASS::init();

        $styles = $this->get_static_property(self::STYLES_PROPERTY);
        $this->assertArrayHasKey('aurora-design-style-aurora-design', $styles);
    }

    /**
     * @test
     * @covers ::init
     * 固定ページでスタイルが追加されるか
     */
    public function test_init_adds_styles_for_page(): void
    {
        $page_id = $this->factory()->post->create(['post_type' => 'page']);
        $this->go_to(get_permalink($page_id));

        self::TARGET_CLASS::init();

        $styles = $this->get_static_property(self::STYLES_PROPERTY);
        $this->assertArrayHasKey('aurora-design-style-aurora-design', $styles);
    }

    /**
     * @test
     * @covers ::init
     * フロントページかつホームではない場合
     */
    public function test_init_adds_styles_for_front_page_not_home(): void
    {
        update_option('page_on_front', $this->factory()->post->create(['post_type' => 'page']));
        update_option('show_on_front', 'page');
        $this->go_to(home_url('/'));

        self::TARGET_CLASS::init();

        $styles = $this->get_static_property(self::STYLES_PROPERTY);
        $this->assertArrayHasKey('aurora-design-style-aurora-design', $styles);
    }

    /**
     * @test
     * @covers ::init
     * アーカイブページでスタイルが追加されるか
     */
    public function test_init_adds_styles_for_archive(): void
    {
        $this->go_to(get_post_type_archive_link('post'));
        $GLOBALS['wp_query']->is_archive = true; // ここでフラグをセット

        self::TARGET_CLASS::init();

        $styles = $this->get_static_property(self::STYLES_PROPERTY);
        $this->assertArrayHasKey('aurora-design-style-aurora-design', $styles);
    }

    /**
     * @test
     * @covers ::init
     * 検索ページでスタイルが追加されるか
     */
    public function test_init_adds_styles_for_search(): void
    {
        $this->go_to(home_url('/?s=test'));

        self::TARGET_CLASS::init();

        $styles = $this->get_static_property(self::STYLES_PROPERTY);
        $this->assertArrayHasKey('aurora-design-blocks-style-module', $styles);
    }

    /**
     * @test
     * @covers ::init
     * 404ページでスタイルが追加されるか
     */
    public function test_init_adds_styles_for_404(): void
    {
        $this->go_to(home_url('/non-existing-page'));
        $GLOBALS['wp_query']->is_404 = true; // ここでフラグをセット

        self::TARGET_CLASS::init();

        $styles = $this->get_static_property(self::STYLES_PROPERTY);
        $this->assertArrayHasKey('aurora-design-style-aurora-design', $styles);
    }
    /**
     * @test
     * @covers ::init
     * EditorStyles は常にセットされる
     */
    public function test_init_sets_editor_styles(): void
    {
        self::TARGET_CLASS::init_forEditor();

        $editor_styles = $this->get_static_property(self::EDITOR_STYLES_PROPERTY);
        $this->assertArrayHasKey('aurora-design-style-awesome', $editor_styles);
    }

    /**
     * @test
     * @covers ::init
     * DeferredStyles は常に awesome が含まれる
     */
    public function test_init_sets_deferred_styles(): void
    {
        self::TARGET_CLASS::init();

        $reflection = new ReflectionClass(self::TARGET_CLASS);
        $property   = $reflection->getProperty(self::DEFERRED_STYLES_PROPERTY);
        $property->setAccessible(true);
        $deferred_styles = $property->getValue();

        $this->assertContains('aurora-design-style-awesome', $deferred_styles);
        $this->assertContains('aurora-design-style-aurora-design', $deferred_styles);
        $this->assertContains('aurora-design-blocks-style-module', $deferred_styles);
    }

    // -----------------------
    // Reflection helpers
    // -----------------------

    private function reset_static_property(string $property, $value): void
    {
        $reflection = new ReflectionClass(self::TARGET_CLASS);
        $prop       = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue(null, $value);
    }

    private function get_static_property(string $property)
    {
        $reflection = new ReflectionClass(self::TARGET_CLASS);
        $prop       = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue();
    }
}
