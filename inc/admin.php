<?php
if (! defined('ABSPATH')) exit;

class Aurora_Admin
{

    private static $instance = null;
    private $tabs;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // タブマネージャーを初期化
        $this->tabs = new Aurora_Admin_Tabs();

        // 管理画面メニューを追加
        add_action('admin_menu', [$this, 'add_menu']);
    }

    public function add_menu()
    {
        add_menu_page(
            __('Aurora Design Blocks', 'aurora-design-blocks'), // ページタイトル
            __('Aurora Design Blocks', 'aurora-design-blocks'), // メニュータイトル
            'manage_options',                                    // 権限
            'aurora-design-blocks',                              // スラッグ
            [$this, 'render'],                                   // コールバック
            'dashicons-layout',                                  // アイコン
            60                                                   // 表示順
        );
    }

    /**
     * メインページをレンダリング
     */
    public function render()
    {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : $this->tabs->get_default();

?>
        <div class="wrap">
            <h1><?php echo esc_html__('Aurora Design Blocks', 'aurora-design-blocks'); ?></h1>

            <nav class="nav-tab-wrapper">
                <?php $this->tabs->render_tabs($tab); ?>
            </nav>

            <div class="tab-content">
                <?php $this->tabs->render_page($tab); ?>
            </div>
        </div>
    <?php
    }
}




if (! defined('ABSPATH')) exit;

class Aurora_Admin_Tabs
{

    private $tabs = [];

    public function __construct()
    {
        // タブ一覧を登録
        $this->tabs = [
            'about'    => new Aurora_Admin_Page_About(),
            'settings' => new Aurora_Admin_Page_Settings(),
            'gemini_ai' => new Aurora_Admin_Page_CreateSlag(), // ★★★ 新規追加 ★★★
        ];
    }

    /**
     * デフォルトタブを返す
     */
    public function get_default()
    {
        return 'about';
    }

    /**
     * タブリンクを描画
     */
    public function render_tabs($current)
    {
        foreach ($this->tabs as $key => $obj) {
            $active = $current === $key ? 'nav-tab-active' : '';
            echo '<a href="?page=aurora-design-blocks&tab=' . esc_attr($key) . '" class="nav-tab ' . $active . '">'
                . esc_html($obj->get_label()) .
                '</a>';
        }
    }

    /**
     * タブページを描画
     */
    public function render_page($current)
    {
        if (isset($this->tabs[$current])) {
            $this->tabs[$current]->render_page();
        } else {
            echo '<p>' . esc_html__('Tab not found.', 'aurora-design-blocks') . '</p>';
        }
    }
}



if (! defined('ABSPATH')) exit;

class Aurora_Admin_Page_About
{

    public function get_label()
    {
        return __('Overview', 'aurora-design-blocks');
    }

    public function render_page()
    {
    ?>
        <h2><?php echo esc_html__('Overview', 'aurora-design-blocks'); ?></h2>
        <p><?php echo esc_html__('Welcome to the Aurora Design Blocks setting screen. Here you can manage the various features.', 'aurora-design-blocks'); ?></p>

        <h3><?php echo esc_html__('Feature Management', 'aurora-design-blocks'); ?></h3>
        <p><?php echo esc_html__('Features such as OGP and Table of Contents can be enabled or disabled in the "Enable/Disable Settings" tab.', 'aurora-design-blocks'); ?></p>
        <p><?php echo esc_html__('- OGP (Open Graph Protocol): When enabled, OGP meta tags are automatically output to the header.', 'aurora-design-blocks'); ?></p>
        <p><?php echo esc_html__('- Table of Contents (TOC): When enabled, a table of contents is automatically generated and displayed at the beginning of each Page and Post when the website is viewed.', 'aurora-design-blocks'); ?></p>
        <p><?php echo esc_html__('Additionally, you can control whether the Table of Contents is output on a per-page basis using the settings section located at the bottom right of each page.
', 'aurora-design-blocks'); ?></p>
        <h3><?php echo esc_html__('Customizer Settings', 'aurora-design-blocks'); ?></h3>
        <p><?php echo esc_html__('The following functions are managed in the WordPress Customizer:', 'aurora-design-blocks'); ?></p>
        <ul>
            <li><?php echo esc_html__('- Google Analytics', 'aurora-design-blocks'); ?></li>
            <li><?php echo esc_html__('- Google Tag Manager', 'aurora-design-blocks'); ?></li>
            <li><?php echo esc_html__('- Google AdSense', 'aurora-design-blocks'); ?></li>
        </ul>
        <p>
            <?php
            // カスタマイザーへのリンクを動的に取得・表示する場合（WordPressの場合）
            $customizer_link = admin_url('customize.php');
            printf(
                esc_html__('Configure these settings in the %sCustomizer%s.', 'aurora-design-blocks'),
                '<a href="' . esc_url($customizer_link) . '">',
                '</a>'
            );
            ?>
        </p>

        <h3><?php echo esc_html__('Other features', 'aurora-design-blocks'); ?></h3>
        <p>
            <?php echo esc_html__('For detailed instructions, please refer to the', 'aurora-design-blocks'); ?>
            <a href="https://integlight.auroralab-design.com/aurora-design-blocks/"
                target="_blank"
                rel="noopener">
                <?php echo esc_html__('[manual page]', 'aurora-design-blocks'); ?>
            </a>
        </p>
    <?php
    }
}




if (! defined('ABSPATH')) exit;

class Aurora_Admin_Page_Settings
{

    public function __construct()
    {
        // 設定を登録
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function get_label()
    {
        return __('Enable / Disable Settings', 'aurora-design-blocks');
    }

    public function register_settings()
    {

        register_setting(
            'aurora_settings_group',
            'aurora_design_blocks_options',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
                'default' => [
                    'ogp_enabled' => '1',
                    'toc_enabled' => '1',
                ],
            ]
        );
    }

    /**
     * 設定値をサニタイズ
     */
    public function sanitize($input)
    {
        $out = [];

        $out['ogp_enabled'] = isset($input['ogp_enabled']) && $input['ogp_enabled'] === '1' ? '1' : '0';
        $out['toc_enabled'] = isset($input['toc_enabled']) && $input['toc_enabled'] === '1' ? '1' : '0';

        return $out;
    }

    /**
     * 設定ページを描画
     */
    public function render_page()
    {

        $options = get_option('aurora_design_blocks_options', [
            'ogp_enabled' => '1',
            'toc_enabled' => '1',
        ]);
    ?>
        <h2><?php echo esc_html__('Enable / Disable Features', 'aurora-design-blocks'); ?></h2>

        <form method="post" action="options.php">
            <?php settings_fields('aurora_settings_group'); ?>

            <table class="form-table">

                <tr>
                    <th><?php echo esc_html__('OGP Generation', 'aurora-design-blocks'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                name="aurora_design_blocks_options[ogp_enabled]"
                                value="1"
                                <?php checked($options['ogp_enabled'], '1'); ?> />
                            <?php echo esc_html__('Enable', 'aurora-design-blocks'); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th><?php echo esc_html__('Automatic Table of Contents', 'aurora-design-blocks'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                name="aurora_design_blocks_options[toc_enabled]"
                                value="1"
                                <?php checked($options['toc_enabled'], '1'); ?> />
                            <?php echo esc_html__('Enable', 'aurora-design-blocks'); ?>
                        </label>
                    </td>
                </tr>

            </table>

            <?php submit_button(); ?>
        </form>
    <?php
    }
}


Aurora_Admin::get_instance();



class Aurora_Admin_Page_CreateSlag
{
    private $option_group = 'aurora_gemini_ai_group';
    private $option_name = 'aurora_gemini_ai_options';

    public function __construct()
    {
        // 設定を登録
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function get_label()
    {
        return __('Gemini AI Settings', 'aurora-design-blocks'); // タブのラベル
    }

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

        // 1. 有効/無効のチェックボックス (デフォルト無効)
        $out['ai_slug_enabled'] = isset($input['ai_slug_enabled']) && $input['ai_slug_enabled'] === '1' ? '1' : '0';

        // 2. APIキーのサニタイズ (文字列として安全に処理)
        // APIキーは長いため、通常のテキストフィールドとしてサニタイズ
        $out['api_key'] = isset($input['api_key']) ? sanitize_text_field(trim($input['api_key'])) : '';

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
        $key = isset($options['api_key']) ? $options['api_key'] : '';

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
