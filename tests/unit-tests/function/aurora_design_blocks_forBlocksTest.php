<?php

class aurora_design_blocks_forBlocksTest extends WP_UnitTestCase
{
    protected $expectedIncorrectUsage = '';

    public function setUp(): void
    {
        parent::setUp();

        // フィルターでエラーを無視
        //add_filter('doing_it_wrong_run', '__return_false');

        // 対象ファイル読み込みとフック実行
        //require_once dirname(__DIR__, 3) . '/inc/aurora-design-blocks.php';
        //do_action('init');
        //do_action('enqueue_block_editor_assets');
    }

    public function tearDown(): void
    {


        parent::tearDown();
    }

    public function test_register_custom_blocks_registers_blocks()
    {

        $block = WP_Block_Type_Registry::get_instance()->get_registered('aurora-design-blocks/cta-block');

        $this->assertNotNull($block, 'sample-block should be registered.');
    }

    public function test_enqueue_block_assets_translation_hooks()
    {
        global $wp_scripts;

        $this->assertIsObject(
            $wp_scripts->query('aurora-design-blocks-cta-block-editor-script', 'registered'),
            '@@@@@@@@@@@@@@@@@@@@@@@@'
        );

        /*
        $this->assertIsObject(
            $wp_scripts->query('aurora-design-blocks-custom-cover-editor-script', 'registered'),
            'custom-cover-block script should be registered'
        );
        */

        $this->assertIsObject(
            $wp_scripts->query('aurora-design-blocks-tab-block-editor-script', 'registered'),
            'tab-block script should be registered'
        );
    }
}
