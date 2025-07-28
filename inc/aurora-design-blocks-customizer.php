<?php


/*get_theme_modを使っていた不具合対応 一時的なコード*/
class AuroraDesignBlocks_forFront
{

    public static function outCode($code_name)
    {
        $tracking_code = get_option($code_name);

        if (empty($tracking_code)) {
            $tracking_code = get_theme_mod($code_name);
        }

        if (!empty($tracking_code)) {
            echo $tracking_code;
        }
    }
}



// ## Google_Analytics _s /////////////////////////////////////////////



class AuroraDesignBlocks_customizer_ga
{

    // コンストラクタ：カスタマイザー設定の登録
    public function __construct()
    {
        add_action('customize_register', array($this, 'regSettings'));
        add_action('wp_head', array($this, 'outCode'));
    }

    // カスタマイザーに設定項目を登録
    public function regSettings($wp_customize)
    {
        // Google Analytics 設定セクションを追加
        $wp_customize->add_section('auroraDesignBlocks_ga_section', array(
            'title' => __('Google Analytics Setting', 'aurora-design-blocks'),
            'priority' => 1000,
        ));

        // Google Analytics トラッキングコードを入力する設定を追加
        $wp_customize->add_setting('auroraDesignBlocks_ga_trackingCode', array(
            'default' => '',
            'sanitize_callback' =>  [$this, 'auroraDesignBlocks_innocuousSanitize'], // 無害なサニタイズ関数を適用
            'type' => 'option',

        ));

        // トラッキングコード入力フィールドを追加
        $wp_customize->add_control('auroraDesignBlocks_ga_trackingCode', array(
            'label' => __('Google Analytics Tracking Code', 'aurora-design-blocks'),
            'section' => 'auroraDesignBlocks_ga_section',
            'type' => 'textarea', // 複数行のテキストエリアを使用
            'description' => __('Please paste the entire tracking code provided by Google Analytics.', 'aurora-design-blocks'),

        ));
    }

    public function auroraDesignBlocks_innocuousSanitize($value)
    {

        return $value;
    }

    // Google アナリティクスコードをサイトの <head> に出力
    public function outCode()
    {
        AuroraDesignBlocks_forFront::outCode('auroraDesignBlocks_ga_trackingCode');
    }
}

// クラスをインスタンス化して処理を開始
new AuroraDesignBlocks_customizer_ga();
// ## Google_Analytics _e /////////////////////////////////////////////


// ## Google_GTM _s /////////////////////////////////////////////
class AuroraDesignBlocks_customizer_gtm
{

    // コンストラクタ：カスタマイザー設定の登録
    public function __construct()
    {
        add_action('customize_register', array($this, 'regSettings'));
        add_action('wp_head', array($this, 'outCode'));
        add_action('wp_body_open', array($this, 'outNoscriptCode')); // body開始直後に追加
    }

    // カスタマイザーに設定項目を登録
    public function regSettings($wp_customize)
    {
        // Google Tag Manager 設定セクションを追加
        $wp_customize->add_section('auroraDesignBlocks_gtm_section', array(
            'title' => __('Google Tag Manager Setting', 'aurora-design-blocks'),
            'priority' => 1000,
        ));

        // Google Tag Manager トラッキングコードを入力する設定を追加
        $wp_customize->add_setting('auroraDesignBlocks_gtm_trackingCode', array(
            'default' => '',
            'sanitize_callback' =>  [$this, 'auroraDesignBlocks_innocuousSanitize'], // 無害なサニタイズ関数を適用
            'type' => 'option',

        ));

        // GTM トラッキングコード入力フィールドを追加
        $wp_customize->add_control('auroraDesignBlocks_gtm_trackingCode', array(
            'label' => __('Code to output in the <head> tag', 'aurora-design-blocks'),
            'section' => 'auroraDesignBlocks_gtm_section',
            'type' => 'textarea', // 複数行のテキストエリアを使用
            'description' => __('Please paste the code provided by Google Tag Manager.', 'aurora-design-blocks'),
        ));

        // Google Tag Manager noscript バックアップコードを入力する設定を追加
        $wp_customize->add_setting('auroraDesignBlocks_gtm_noscriptCode', array(
            'default' => '',
            'sanitize_callback' => [$this, 'auroraDesignBlocks_innocuousSanitize'], // 無害なサニタイズ関数を適用
            'type' => 'option',

        ));


        // noscript トラッキングコード入力フィールドを追加
        $wp_customize->add_control('auroraDesignBlocks_gtm_noscriptCode', array(
            'label' => __('Code to output immediately after the opening <body> tag', 'aurora-design-blocks'),
            'section' => 'auroraDesignBlocks_gtm_section',
            'type' => 'textarea',
            'description' => __('Please paste the code provided by Google Tag Manager.', 'aurora-design-blocks'),
        ));
    }

    // Google Tag Manager コードをサイトの <head> に出力

    public function auroraDesignBlocks_innocuousSanitize($value)
    {

        return $value;
    }

    // Google Tag Manager コードをサイトの <head> に出力
    public function outCode()
    {

        AuroraDesignBlocks_forFront::outCode('auroraDesignBlocks_gtm_trackingCode');
    }

    // Google Tag Manager noscript バックアップコードを <body> タグ直後に出力
    public function outNoscriptCode()
    {
        AuroraDesignBlocks_forFront::outCode('auroraDesignBlocks_gtm_noscriptCode');
    }
}

// クラスをインスタンス化して処理を開始
new AuroraDesignBlocks_customizer_gtm();
// ## Google_GTM _e /////////////////////////////////////////////


// ## Google_adSense _s /////////////////////////////////////////////
class AuroraDesignBlocks_customizer_adsense_auto
{
    public function __construct()
    {
        add_action('customize_register', [$this, 'regSettings']);
        add_action('wp_head', [$this, 'outCode']);
    }

    public function regSettings($wp_customize)
    {
        $wp_customize->add_section('auroraDesignBlocks_adsense_section', [
            'title' => __('Google AdSense Auto Ads', 'aurora-design-blocks'),
            'priority' => 1001,
        ]);

        $wp_customize->add_setting('auroraDesignBlocks_adsense_code', [
            'default' => '',
            'sanitize_callback' => [$this, 'allow_script_tags'],
            'type' => 'option',
        ]);

        $wp_customize->add_control('auroraDesignBlocks_adsense_code', [
            'label' => __('AdSense Auto Ads Code', 'aurora-design-blocks'),
            'section' => 'auroraDesignBlocks_adsense_section',
            'type' => 'textarea',
            'description' => __('Paste the entire AdSense auto ads code here.', 'aurora-design-blocks'),
        ]);
    }

    public function allow_script_tags($value)
    {
        return $value; // スクリプトタグを許可するためサニタイズなし（要注意）
    }

    public function outCode()
    {
        AuroraDesignBlocks_forFront::outCode('auroraDesignBlocks_adsense_code');
    }
}

new AuroraDesignBlocks_customizer_adsense_auto();
// ## Google_adSense _e /////////////////////////////////////////////
