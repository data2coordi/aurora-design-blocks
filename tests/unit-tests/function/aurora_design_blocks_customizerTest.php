
<?php

class aurora_design_blocks_customizerTest extends WP_UnitTestCase
{
    protected $ga;
    protected $gtm;
    protected $adsense;

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
        $this->adsense = new AuroraDesignBlocks_customizer_adsense_auto();
    }

    public function tearDown(): void
    {
        // テーマモッドのクリア
        delete_option('auroraDesignBlocks_ga_trackingCode');
        delete_option('auroraDesignBlocks_gtm_trackingCode');
        delete_option('auroraDesignBlocks_gtm_noscriptCode');
        delete_option('auroraDesignBlocks_adsense_code'); // 追加

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
        update_option('auroraDesignBlocks_ga_trackingCode', 'GAコード');

        ob_start();                  // ← ここでバッファ開始
        $this->ga->outCode();        // フック登録
        do_action('wp_head');        // フック呼び出し
        do_action('wp_footer');        // フック呼び出し
        $output = ob_get_clean();    // 出力取得

        $this->assertStringContainsString('GAコード', $output);
    }
    public function test_ga_outCode_outputs_nothing_when_no_code()
    {
        update_option('auroraDesignBlocks_ga_trackingCode', '');

        ob_start();                  // ← ここでバッファ開始
        $this->ga->outCode();        // フック登録
        do_action('wp_head');        // フック呼び出し
        $output = ob_get_clean();    // 出力取得

        // GAコードだけをチェックしたい場合は：
        $this->assertStringNotContainsString('<script>GAコード</script>', $output);

        // 完全に何も出さないことを確認する場合は、
        // テスト環境をwp_headの最小化環境にする必要あり
    }

    public function test_gtm_outCode_outputs_tracking_code()
    {
        $tracking_code = '<script>GTMコード</script>';
        update_option('auroraDesignBlocks_gtm_trackingCode', $tracking_code);

        ob_start();
        $this->gtm->outCode();
        $output = ob_get_clean();

        $this->assertStringContainsString($tracking_code, $output);
    }

    public function test_gtm_outCode_outputs_nothing_when_no_code()
    {
        update_option('auroraDesignBlocks_gtm_trackingCode', '');

        ob_start();
        $this->gtm->outCode();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    public function test_gtm_outNoscriptCode_outputs_noscript_code()
    {
        $noscript_code = '<noscript>バックアップコード</noscript>';
        update_option('auroraDesignBlocks_gtm_noscriptCode', $noscript_code);

        ob_start();
        $this->gtm->outNoscriptCode();
        $output = ob_get_clean();

        $this->assertStringContainsString($noscript_code, $output);
    }

    public function test_gtm_outNoscriptCode_outputs_nothing_when_no_code()
    {
        update_option('auroraDesignBlocks_gtm_noscriptCode', '');

        ob_start();
        $this->gtm->outNoscriptCode();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    //adsense用のテスト



    public function test_adsense_customizer_registration()
    {
        $wp_customize = new WP_Customize_Manager();

        $this->adsense->regSettings($wp_customize);

        // セクションが登録されているか
        $this->assertArrayHasKey('auroraDesignBlocks_adsense_section', $wp_customize->sections());

        // 設定が登録されているか
        $this->assertArrayHasKey('auroraDesignBlocks_adsense_code', $wp_customize->settings());

        // コントロールが登録されているか
        $this->assertArrayHasKey('auroraDesignBlocks_adsense_code', $wp_customize->controls());
    }

    public function test_adsense_outCode_outputs_code()
    {
        $adsense_code = '<script data-ad-client="ca-pub-xxxxx"></script>';
        update_option('auroraDesignBlocks_adsense_code', $adsense_code);

        ob_start();
        $this->adsense->outCode();
        $output = ob_get_clean();

        $this->assertStringContainsString($adsense_code, $output);
    }

    public function test_adsense_outCode_outputs_nothing_when_no_code()
    {
        update_option('auroraDesignBlocks_adsense_code', '');

        ob_start();
        $this->adsense->outCode();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }
}
