<?php

class aurora_design_blocks_outerAssetsTest extends WP_UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト対象クラスを読み込む場合があればここで require_once
        // require_once dirname(__DIR__, 3) . '/inc/class-aurora-design-blocks-defer-css.php';

        // 初期化
        AuroraDesignBlocksDeferCss::add_deferred_styles([]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_defer_css_returns_modified_tag_when_handle_is_deferred()
    {
        AuroraDesignBlocksDeferCss::add_deferred_styles(['test-style']);

        $original = "<link rel='stylesheet' id='test-style-css' href='style.css' type='text/css' media='all' />";
        $result = AuroraDesignBlocksDeferCss::defer_css($original, 'test-style');

        $this->assertStringContainsString("media='print'", $result);
        $this->assertStringContainsString("onload=\"this.media='all'\"", $result);
    }

    public function test_defer_css_returns_original_tag_when_handle_is_not_deferred()
    {
        $original = "<link rel='stylesheet' id='non-deferred-css' href='style.css' type='text/css' media='all' />";
        $result = AuroraDesignBlocksDeferCss::defer_css($original, 'non-deferred-style');

        $this->assertSame($original, $result);
    }
}
