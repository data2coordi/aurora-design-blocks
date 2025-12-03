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
        <p><?php echo esc_html__('Aurora Design Blocks is a collection of blocks registered as an official theme.', 'aurora-design-blocks'); ?></p>
        <p><?php echo esc_html__('This setup page allows you to manage site-wide feature settings.', 'aurora-design-blocks'); ?></p>

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
