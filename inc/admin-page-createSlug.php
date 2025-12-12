<?php


/**
 * 暗号化/復号化処理を担う共通セキュリティヘルパークラス
 */
class AuroraDesignBlocks_Security_Helper
{

    /**
     * 文字列を暗号化するヘルパー関数 (IVをデータに含める)
     *
     * @param string $data 暗号化するデータ.
     * @return string 暗号化されたデータ (IVと暗号文をBase64エンコードして連結).
     */
    public static function encrypt_key($data)
    {

        // ログステップ 1: 入力データチェック
        if (empty($data)) {
            return '';
        }

        // ※ base64_decode($data, true) を使用することで、デコード可能なデータかを厳密にチェックします。
        if (base64_decode($data, true) !== false) {
            return $data; // 既にBase64形式なので、そのまま返す
        }

        // ログステップ 2: 鍵の存在チェック
        $key = defined('AUTH_KEY') ? AUTH_KEY : '';
        if (empty($key)) {
            return $data;
        }
        // 警告: デバッグのため鍵の内容を出力 (極めて危険)

        $cipher = 'aes-256-cbc';
        $iv_len = openssl_cipher_iv_length($cipher);

        // ログステップ 3: IV生成
        $iv = openssl_random_pseudo_bytes($iv_len, $crypto_strong);
        if ($iv === false || !$crypto_strong) {
            return $data;
        }

        // ログステップ 4: 暗号化実行
        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            while ($msg = openssl_error_string()) {
            }
            return $data;
        }
        // 警告: デバッグのため暗号文の内容を出力

        // ログステップ 5: IVと暗号文の結合とBase64エンコード
        $combined_data = $iv . $encrypted;
        $result_base64 = base64_encode($combined_data);

        // 警告: デバッグのため最終的なBase64文字列の内容を出力

        return $result_base64;
    }

    /**
     * 文字列を復号化するヘルパー関数 (データからIVを抽出)
     *
     * @param string $data 復号化するデータ (IVと暗号文が連結されBase64エンコードされたもの).
     * @return string 復号化されたデータ.
     */
    public static function decrypt_key($data)
    {
        // ログステップ 1: 入力データチェック
        if (empty($data)) {
            return '';
        }

        // ログステップ 2: 鍵の存在チェック
        $key = defined('AUTH_KEY') ? AUTH_KEY : '';
        if (empty($key)) {
            return $data;
        }
        // 警告: デバッグのため鍵の内容を出力 (極めて危険)

        $cipher = 'aes-256-cbc';
        $iv_len = openssl_cipher_iv_length($cipher);

        // ログステップ 3: Base64デコード
        $decoded = base64_decode($data, true);

        if ($decoded === false) {
            return $data;
        }
        $decoded_len = strlen($decoded);
        // 警告: デバッグのためデコード後のバイナリデータ（先頭部分）を出力

        // ログステップ 4: データ長チェック
        if ($decoded_len < $iv_len) {
            return $data;
        }

        // ログステップ 5: IVと暗号文の分離
        $iv = substr($decoded, 0, $iv_len);
        $encrypted = substr($decoded, $iv_len);

        // ログステップ 6: 復号化実行
        $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            while ($msg = openssl_error_string()) {
            }
            return $data;
        }


        return $decrypted;
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
        // ★★★ エラー表示フックを追加 ★★★
        add_action('admin_notices', [$this, 'display_last_api_error']);
    }

    public function get_label()
    {
        return __('Auto Slug Create Settings', 'aurora-design-blocks'); // タブのラベル
    }

    /**
     * ★★★ 投稿処理で発生したAPIエラーを管理画面上部に表示 ★★★
     */
    /**
     * ★★★ 投稿処理で発生したAPIエラーを管理画面上部に表示 ★★★
     */
    public function display_last_api_error()
    {
        // ユーザーが自分のプラグインの設定ページにいる場合に限定して表示
        if (!isset($_GET['page']) || strpos($_GET['page'], 'aurora-design-blocks') === false) {
            return;
        }

        $last_error = get_option('adb_gemini_last_error');

        if (!empty($last_error)) {
            // エラーを一度表示したら削除して、管理画面をリロードするたびに表示されないようにする
            delete_option('adb_gemini_last_error');

            // 基本となるエラーメッセージ
            $base_message = __('**Automatic Slug Generation Failed** (Gemini API Error): Your post title was not converted to an English slug due to an API issue.', 'aurora-design-blocks');

            // 補足メッセージ
            $supplement_message = __('Possible causes include: **incorrect API key**, or **exceeding the daily usage limit** of the Gemini API.', 'aurora-design-blocks');

            // 詳細なエラーメッセージ
            $detail_message = sprintf(
                __('Error details: %s', 'aurora-design-blocks'),
                esc_html($last_error)
            );

            // WordPressのエラー通知として表示
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>' . esc_html__('AI Slug Generation Status', 'aurora-design-blocks') . '</strong></p>';
            echo '<p>' . $base_message . '</p>';
            echo '<p><strong>' . $supplement_message . '</strong></p>'; // ★ここを太字で強調
            echo '<p class="description">' . $detail_message . '</p>';
            echo '</div>';
        }
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
    // ... (中略)

    /**
     * 設定値をサニタイズ
     */
    public function sanitize($input)
    {
        $out = [];
        // 現在の保存値を取得
        $current_options = get_option($this->option_name);

        // 1. 機能有効/無効の処理
        if (isset($input['ai_slug_enabled']) && $input['ai_slug_enabled'] === '1') {
            $out['ai_slug_enabled'] = '1';
        } else {
            $out['ai_slug_enabled'] = '0';
        }

        // 2. APIキーの処理 (前回修正したロジック)
        $new_key = isset($input['api_key']) ? trim($input['api_key']) : '';

        // 新しいキーの入力があれば暗号化
        if (!empty($new_key)) {
            $sanitized_key = sanitize_text_field($new_key);
            $out['api_key'] = AuroraDesignBlocks_Security_Helper::encrypt_key($sanitized_key);
        } elseif (isset($input['api_key']) && $input['api_key'] === '') {
            // 入力が空で送信された場合、キーを削除
            $out['api_key'] = '';
        } else {
            // キーの入力がない場合、現在の暗号化されたキーを保持
            $out['api_key'] = isset($current_options['api_key']) ? $current_options['api_key'] : '';
        }

        // 3. ★【追加するロジック】キーがない場合の有効化を禁止
        // 機能が有効化されており (ai_slug_enabled === '1')、かつ
        // 最終的に保存されるAPIキーが空の場合 (empty($out['api_key']))
        if ($out['ai_slug_enabled'] === '1' && empty($out['api_key'])) {

            // 強制的に機能を無効化する
            $out['ai_slug_enabled'] = '0';

            // ユーザーにエラーメッセージを表示する
            add_settings_error(
                $this->option_name,
                'api_key_required',
                __('To enable automatic slug generation, the Gemini API Key is required. The function has been disabled.', 'aurora-design-blocks'),
                'error'
            );
        }

        return $out;
    }
// ... (中略)
    /**
     * 設定ページを描画
     */
    public function render_page()
    {
?>

        <form method="post" action="options.php">
            <?php
            settings_errors();
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
        echo esc_html__('Enable automatic slug generation using Gemini AI (It is generated only the first time you publish a new post)', 'aurora-design-blocks');
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