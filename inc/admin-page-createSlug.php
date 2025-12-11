<?php


/**
 * 暗号化/復号化処理を担う共通セキュリティヘルパークラス
 */
class AuroraDesignBlocks_Security_Helper
{
    /**
     * 文字列を暗号化するヘルパー関数 (IVをデータに含める)
     * * @param string $data 暗号化するデータ.
     * @return string 暗号化されたデータ (IVと暗号文をBase64エンコードして連結).
     */
    public static function encrypt_key($data)
    {
        if (empty($data)) {
            return '';
        }

        $key = defined('AUTH_KEY') ? AUTH_KEY : '';
        if (empty($key)) {
            return $data;
        }

        $cipher = 'aes-256-cbc';
        $iv_len = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($iv_len);

        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            return $data;
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * 文字列を復号化するヘルパー関数 (データからIVを抽出)
     *
     * @param string $data 復号化するデータ (IVと暗号文が連結されBase64エンコードされたもの).
     * @return string 復号化されたデータ.
     */
    public static function decrypt_key($data)
    {
        if (empty($data)) {
            return '';
        }

        $key = defined('AUTH_KEY') ? AUTH_KEY : '';
        if (empty($key)) {
            return $data;
        }

        $cipher = 'aes-256-cbc';
        $iv_len = openssl_cipher_iv_length($cipher);

        $decoded = base64_decode($data);

        if (strlen($decoded) < $iv_len) {
            return $data;
        }

        $iv = substr($decoded, 0, $iv_len);
        $encrypted = substr($decoded, $iv_len);

        $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        return $decrypted !== false ? $decrypted : $data;
    }
}



/**
 * タブページクラス: AuroraDesignBlocks_AdminPage_CreateSlug (新規タブ)
 */
class AuroraDesignBlocks_AdminPage_CreateSlug
{
    private $option_group = 'aurora_gemini_ai_group';
    private $option_name = 'aurora_gemini_ai_options';



    /**
     * [新規メソッド] WordPressのフック経由で自身を AuroraDesignBlocks_AdminTabs に登録する
     */
    public static function register_hooks()
    {
        add_action('admin_init', function () {
            $tabs_manager = AuroraDesignBlocks_AdminTop::get_instance()->tabs;
            $tabs_manager->add_tab('gemini_ai', self::class);
        }, 1);
    }

    public function __construct()
    {
        // 設定を登録
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function get_label()
    {
        return __('Gemini AI Settings', 'aurora-design-blocks'); // タブのラベル
    }

    // ... （中略：register_settings, sanitize, render_page, field_render メソッドは変更なし）
    public function register_settings()
    {
        // 設定グループとオプション名を登録
        register_setting(
            $this->option_group,
            $this->option_name,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
                'default' => [
                    'ai_slug_enabled' => '0', // デフォルトは無効
                    'api_key' => '',
                ],
            ]
        );

        // 設定セクションの追加
        add_settings_section(
            'gemini_ai_section',
            __('Automatic Slug Generation Settings', 'aurora-design-blocks'),
            null, // コールバック不要
            $this->option_group
        );

        // 機能有効/無効のフィールド
        add_settings_field(
            'ai_slug_enabled',
            __('Enable AI Slug Generation', 'aurora-design-blocks'),
            [$this, 'render_ai_slug_enabled_field'],
            $this->option_group,
            'gemini_ai_section'
        );

        // APIキー入力フィールド
        add_settings_field(
            'api_key',
            __('Gemini API Key', 'aurora-design-blocks'),
            [$this, 'render_api_key_field'],
            $this->option_group,
            'gemini_ai_section'
        );
    }

    /**
     * 設定値をサニタイズ
     */
    public function sanitize($input)
    {
        $out = [];
        $current_options = get_option($this->option_name); // 現在の保存値を取得

        // ... (ai_slug_enabled の処理は省略)

        // 2. APIキーの処理
        $new_key = isset($input['api_key']) ? trim($input['api_key']) : '';

        // ユーザーが何か新しいキーを入力した場合
        if (!empty($new_key)) {
            $sanitized_key = sanitize_text_field($new_key);
            $out['api_key'] = AuroraDesignBlocks_Security_Helper::encrypt_key($sanitized_key);
        } else {
            // 入力が空の場合、現在の暗号化された値を保持
            $out['api_key'] = isset($current_options['api_key']) ? $current_options['api_key'] : '';
        }

        return $out;
    }
    /**
     * 設定ページを描画
     */
    public function render_page()
    {
?>
        <h2><?php echo esc_html__('Gemini AI Settings', 'aurora-design-blocks'); ?></h2>

        <form method="post" action="options.php">
            <?php
            settings_fields($this->option_group);
            do_settings_sections($this->option_group);
            submit_button();
            ?>
        </form>
<?php
    }

    /**
     * 機能有効/無効チェックボックスのコールバック
     */
    public function render_ai_slug_enabled_field()
    {
        $options = get_option($this->option_name);
        $checked = isset($options['ai_slug_enabled']) ? checked($options['ai_slug_enabled'], '1', false) : '';

        echo '<label>';
        echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[ai_slug_enabled]" value="1"' . $checked . '/>';
        echo esc_html__('Enable automatic slug generation using Gemini AI (only on first save)', 'aurora-design-blocks');
        echo '</label>';
    }

    /**
     * APIキー入力フィールドのコールバック
     */
    public function render_api_key_field()
    {
        $options = get_option($this->option_name);
        $encrypted_key = isset($options['api_key']) ? $options['api_key'] : '';

        // 復号化して表示
        $key = AuroraDesignBlocks_Security_Helper::decrypt_key($encrypted_key);

        echo '<input type="text" name="' . esc_attr($this->option_name) . '[api_key]" value="' . esc_attr($key) . '" size="60" placeholder="AIzaSy..." />';
        echo '<p class="description">';
        printf(
            esc_html__('Enter the API Key obtained from Google AI Studio. %sHow to get the key%s', 'aurora-design-blocks'),
            '<a href="" target="_blank">',
            '</a>'
        );
        echo '</p>';
    }
}

AuroraDesignBlocks_AdminPage_CreateSlug::register_hooks(); // 新しいタブの登録