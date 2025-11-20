<?php

if (! defined('ABSPATH')) exit;


/*フロントにカスタマイザーでセットした値を表示*/
class AuroraDesignBlocks_forFront
{

    public static function outCode($code_name)
    {
        $tracking_code = get_option($code_name);
        if (!empty($tracking_code)) {
            // 【修正箇所】出力時にも必ずサニタイズ処理を適用する
            echo self::sanitize_tracking_code($tracking_code);
        }
    }

    public static function outGA4Id_normal($id_name)
    {
        $tracking_id = get_option($id_name);
        if (empty($tracking_id)) {
            return;
        }

        $tracking_id = sanitize_text_field($tracking_id);

        // 1. GA4 の外部スクリプト（async 読み込み）
        wp_enqueue_script(
            'aurora-ga4',
            "https://www.googletagmanager.com/gtag/js?id={$tracking_id}",
            [],
            AURORA_DESIGN_BLOCKS_VERSION,
            false // フッターではなく head に入れる
        );

        // async 属性を追加
        add_filter('script_loader_tag', function ($tag, $handle, $src) {
            if ($handle === 'aurora-ga4') {
                //    return '<script async src="' . esc_url($src) . '"></script>';
                return str_replace(' src', ' async src', $tag);
            }
            return $tag;
        }, 10, 3);

        // 2. gtag の初期化コード（inline script）
        $inline = "
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{$tracking_id}');
    ";

        wp_add_inline_script('aurora-ga4', $inline, 'after');
    }

    public static function outGA4Id_speedUp($id_name)
    {
        $tracking_id = get_option($id_name);
        if (empty($tracking_id)) return;
        $tracking_id = sanitize_text_field($tracking_id);

        // ダミースクリプト（外部JSはまだ読み込まない）
        wp_register_script('aurora-ga4-speedup', false);
        wp_register_script(
            'aurora-ga4-speedup',
            false,        // URL はまだなし
            array(),
            AURORA_DESIGN_BLOCKS_VERSION,      // ここでバージョンを指定
            true          // フッターに出力
        );






        $inline = "
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        var gtagLoaded = false;

        var loadGtag = function() {
            if (gtagLoaded) return;
            gtagLoaded = true;

            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id={$tracking_id}';
            document.head.appendChild(script);

            gtag('js', new Date());
            gtag('config', '{$tracking_id}');
        };

        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(loadGtag);
        } else {
            setTimeout(loadGtag, 4000);
        }
    ";

        wp_add_inline_script('aurora-ga4-speedup', $inline, 'after');
    }

    /*
     * GoogleトラッキングコードなどのHTMLを安全にサニタイズする共通関数
     * @param string $value ユーザー入力値
     * @return string サニタイズされた値
     */
    public static function sanitize_tracking_code($value)
    {
        // Googleコード全般に必要なタグと属性を最大限に定義した許可リスト
        $allowed_html_for_tracking = [
            'script' => [
                'async' => true,
                'src' => true,
                'type' => true,
                'charset' => true,
                'crossorigin' => true,
                'nonce' => true,
            ],
            'style' => [
                'type' => true,
                'media' => true,
                'scoped' => true,
            ],
            'noscript' => [
                'id' => true,
            ],
            'iframe' => [
                'src' => true,
                'style' => true,
                'width' => true,
                'height' => true,
                'frameborder' => true,
                'scrolling' => true,
                'allow' => true,
                'title' => true,
                'class' => true,
                'id' => true,
                'name' => true,
                'marginwidth' => true,
                'marginheight' => true,
                'loading' => true,
            ],
            'img' => [
                'src' => true,
                'alt' => true,
                'width' => true,
                'height' => true,
                'loading' => true,
                'border' => true,
                'style' => true,
                'class' => true,
                'id' => true,
            ],
            'a' => [
                'href' => true,
                'target' => true,
                'rel' => true,
                'class' => true,
                'id' => true,
            ],
            'div' => [
                'id' => true,
                'class' => true,
                'style' => true,
            ],
            'p' => ['class' => true, 'style' => true,],
            'span' => ['class' => true, 'style' => true,],
            'meta' => ['name' => true, 'content' => true,],
        ];

        return wp_kses($value, $allowed_html_for_tracking);
    }
}



// ## Google_Analytics _s /////////////////////////////////////////////



class AuroraDesignBlocks_customizer_ga
{

    // コンストラクタ：カスタマイザー設定の登録
    public function __construct()
    {
        add_action('customize_register', array($this, 'regSettings'));
        add_action('init', array($this, 'outCode'));
    }

    // カスタマイザーに設定項目を登録
    public function regSettings($wp_customize)
    {
        // Google Analytics 設定セクションを追加
        $wp_customize->add_section('auroraDesignBlocks_ga_section', array(
            'title' => __('Google Analytics Setting', 'aurora-design-blocks'),
            'priority' => 1000,
        ));


        // GA4 Measurement ID を入力する設定を追加
        $wp_customize->add_setting('auroraDesignBlocks_ga_trackingCode', array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field', // IDだけなので安全に
            'type'              => 'option',
        ));

        // テキストフィールドでID入力
        $wp_customize->add_control('auroraDesignBlocks_ga_trackingCode', array(
            'label'       => __('Google Analytics Measurement ID', 'aurora-design-blocks'),
            'section'     => 'auroraDesignBlocks_ga_section',
            'type'        => 'text',
            'description' => __('Enter your Google Analytics Measurement ID (e.g. G-XXXXXXX)', 'aurora-design-blocks'),
        ));

        // 高速化モード設定を追加
        $wp_customize->add_setting('auroraDesignBlocks_ga_optimize', array(
            'default'           => true,
            'sanitize_callback' => 'wp_validate_boolean',
            'type'              => 'option',
        ));

        $wp_customize->add_control('auroraDesignBlocks_ga_optimize', array(
            'label'       => __('Enable Performance Optimization', 'aurora-design-blocks'),
            'section'     => 'auroraDesignBlocks_ga_section',
            'type'        => 'checkbox',
            'description' => __('We prioritize page rendering by deferring the execution of the GA script. This usually does not affect GA measurement.', 'aurora-design-blocks'),
        ));
    }


    // Google アナリティクスコードをサイトの <head>or<body> に出力
    public function outCode()
    {
        $optimize = get_option('auroraDesignBlocks_ga_optimize', true);

        if ($optimize) {
            add_action('wp_footer', function () {
                AuroraDesignBlocks_forFront::outGA4Id_speedUp('auroraDesignBlocks_ga_trackingCode');
            });
        } else {
            add_action('wp_enqueue_scripts', function () {
                AuroraDesignBlocks_forFront::outGA4Id_normal('auroraDesignBlocks_ga_trackingCode');
            });
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
        add_action('wp_footer', array($this, 'outNoscriptCode')); // PF対応!!!body終了直前に追加
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
        return AuroraDesignBlocks_forFront::sanitize_tracking_code($value);
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
        return AuroraDesignBlocks_forFront::sanitize_tracking_code($value);
    }

    public function outCode()
    {
        AuroraDesignBlocks_forFront::outCode('auroraDesignBlocks_adsense_code');
    }
}

new AuroraDesignBlocks_customizer_adsense_auto();
// ## Google_adSense _e /////////////////////////////////////////////
