<?php

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
        $wp_customize->add_section('integlight_ga_section', array(
            'title' => __('Google Analytics Setting', 'integlight'),
            'priority' => 1000,
        ));

        // Google Analytics トラッキングコードを入力する設定を追加
        $wp_customize->add_setting('integlight_ga_trackingCode', array(
            'default' => '',
            'sanitize_callback' =>  [$this, 'integlight_innocuousSanitize'], // 無害なサニタイズ関数を適用

        ));

        // トラッキングコード入力フィールドを追加
        $wp_customize->add_control('integlight_ga_trackingCode', array(
            'label' => __('Google Analytics Tracking Code', 'integlight'),
            'section' => 'integlight_ga_section',
            'type' => 'textarea', // 複数行のテキストエリアを使用
            'description' => __('Please paste the entire tracking code provided by Google Analytics.', 'integlight'),

        ));
    }

    public function integlight_innocuousSanitize($value)
    {

        return $value;
    }

    // Google アナリティクスコードをサイトの <head> に出力
    public function outCode()
    {
        $tracking_code = get_theme_mod('integlight_ga_trackingCode');
        if ($tracking_code) {
            echo $tracking_code; // HTMLをそのまま出力
        }
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
        $wp_customize->add_section('integlight_gtm_section', array(
            'title' => __('Google Tag Manager Setting', 'integlight'),
            'priority' => 1000,
        ));

        // Google Tag Manager トラッキングコードを入力する設定を追加
        $wp_customize->add_setting('integlight_gtm_trackingCode', array(
            'default' => '',
            'sanitize_callback' =>  [$this, 'integlight_innocuousSanitize'], // 無害なサニタイズ関数を適用

        ));

        // GTM トラッキングコード入力フィールドを追加
        $wp_customize->add_control('integlight_gtm_trackingCode', array(
            'label' => __('Code to output in the <head> tag', 'integlight'),
            'section' => 'integlight_gtm_section',
            'type' => 'textarea', // 複数行のテキストエリアを使用
            'description' => __('Please paste the code provided by Google Tag Manager.', 'integlight'),
        ));

        // Google Tag Manager noscript バックアップコードを入力する設定を追加
        $wp_customize->add_setting('integlight_gtm_noscriptCode', array(
            'default' => '',
            'sanitize_callback' => [$this, 'integlight_innocuousSanitize'], // 無害なサニタイズ関数を適用

        ));


        // noscript トラッキングコード入力フィールドを追加
        $wp_customize->add_control('integlight_gtm_noscriptCode', array(
            'label' => __('Code to output immediately after the opening <body> tag', 'integlight'),
            'section' => 'integlight_gtm_section',
            'type' => 'textarea',
            'description' => __('Please paste the code provided by Google Tag Manager.', 'integlight'),
        ));
    }

    // Google Tag Manager コードをサイトの <head> に出力

    public function integlight_innocuousSanitize($value)
    {

        return $value;
    }

    // Google Tag Manager コードをサイトの <head> に出力
    public function outCode()
    {
        $tracking_code = get_theme_mod('integlight_gtm_trackingCode');
        if ($tracking_code) {
            echo $tracking_code; // HTMLをそのまま出力
        }
    }

    // Google Tag Manager noscript バックアップコードを <body> タグ直後に出力
    public function outNoscriptCode()
    {
        $noscript_code = get_theme_mod('integlight_gtm_noscriptCode');
        if ($noscript_code) {
            echo $noscript_code; // noscriptタグを出力
        }
    }
}

// クラスをインスタンス化して処理を開始
new AuroraDesignBlocks_customizer_gtm();
// ## Google_GTM _e /////////////////////////////////////////////
