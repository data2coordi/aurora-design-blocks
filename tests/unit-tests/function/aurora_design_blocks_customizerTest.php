
<?php

class aurora_design_blocks_customizerTest extends WP_UnitTestCase
{
    protected $ga;
    protected $gtm;

    public function setUp(): void
    {
        parent::setUp();
        if (!class_exists('WP_Customize_Manager')) {
            if (!defined('ABSPATH') || !defined('WPINC')) {
                $this->fail('WordPress core constants (ABSPATH, WPINC) are not defined.');
            }
            $customize_manager_path = ABSPATH . WPINC . '/class-wp-customize-manager.php';
            if (!file_exists($customize_manager_path)) {
                $this->fail('WP_Customize_Manager class file not found.');
            }
            require_once $customize_manager_path;
        }

        // 本体クラスをインスタンス化（本来はファイル読み込みが必要）
        $this->ga = new AuroraDesignBlocks_customizer_ga();
        $this->gtm = new AuroraDesignBlocks_customizer_gtm();
    }

    public function tearDown(): void
    {
        // テーマモッドのクリア
        remove_theme_mod('auroraDesignBlocks_ga_trackingCode');
        remove_theme_mod('auroraDesignBlocks_gtm_trackingCode');
        remove_theme_mod('auroraDesignBlocks_gtm_noscriptCode');

        parent::tearDown();
    }

    public function test_ga_customizer_registration()
    {
        $wp_customize = new WP_Customize_Manager();

        $this->ga->regSettings($wp_customize);

        // セクションが登録されているか
        $this->assertArrayHasKey('auroraDesignBlocks_ga_section', $wp_customize->sections());

        // 設定が登録されているか
        $this->assertArrayHasKey('auroraDesignBlocks_ga_trackingCode', $wp_customize->settings());

        // コントロールが登録されているか
        $this->assertArrayHasKey('auroraDesignBlocks_ga_trackingCode', $wp_customize->controls());
    }

    public function test_gtm_customizer_registration()
    {
        $wp_customize = new WP_Customize_Manager();

        $this->gtm->regSettings($wp_customize);

        // セクションが登録されているか
        $this->assertArrayHasKey('auroraDesignBlocks_gtm_section', $wp_customize->sections());

        // 設定が登録されているか
        $this->assertArrayHasKey('auroraDesignBlocks_gtm_trackingCode', $wp_customize->settings());
        $this->assertArrayHasKey('auroraDesignBlocks_gtm_noscriptCode', $wp_customize->settings());

        // コントロールが登録されているか
        $this->assertArrayHasKey('auroraDesignBlocks_gtm_trackingCode', $wp_customize->controls());
        $this->assertArrayHasKey('auroraDesignBlocks_gtm_noscriptCode', $wp_customize->controls());
    }

    public function test_ga_outCode_outputs_tracking_code()
    {
        $tracking_code = '<script>GAコード</script>';
        set_theme_mod('auroraDesignBlocks_ga_trackingCode', $tracking_code);

        // 出力バッファリングで出力をキャプチャ
        ob_start();
        $this->ga->outCode();
        $output = ob_get_clean();

        $this->assertStringContainsString($tracking_code, $output);
    }

    public function test_ga_outCode_outputs_nothing_when_no_code()
    {
        set_theme_mod('auroraDesignBlocks_ga_trackingCode', '');

        ob_start();
        $this->ga->outCode();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    public function test_gtm_outCode_outputs_tracking_code()
    {
        $tracking_code = '<script>GTMコード</script>';
        set_theme_mod('auroraDesignBlocks_gtm_trackingCode', $tracking_code);

        ob_start();
        $this->gtm->outCode();
        $output = ob_get_clean();

        $this->assertStringContainsString($tracking_code, $output);
    }

    public function test_gtm_outCode_outputs_nothing_when_no_code()
    {
        set_theme_mod('auroraDesignBlocks_gtm_trackingCode', '');

        ob_start();
        $this->gtm->outCode();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    public function test_gtm_outNoscriptCode_outputs_noscript_code()
    {
        $noscript_code = '<noscript>バックアップコード</noscript>';
        set_theme_mod('auroraDesignBlocks_gtm_noscriptCode', $noscript_code);

        ob_start();
        $this->gtm->outNoscriptCode();
        $output = ob_get_clean();

        $this->assertStringContainsString($noscript_code, $output);
    }

    public function test_gtm_outNoscriptCode_outputs_nothing_when_no_code()
    {
        set_theme_mod('auroraDesignBlocks_gtm_noscriptCode', '');

        ob_start();
        $this->gtm->outNoscriptCode();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }
}
