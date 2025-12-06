<?php
if (! defined('ABSPATH')) exit;

class AuroraDesignBlocks_Admin
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
        $this->tabs = new AuroraDesignBlocks_Admin_Tabs();

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


AuroraDesignBlocks_Admin::get_instance();



class AuroraDesignBlocks_Admin_Tabs
{

    private $tabs = [];

    public function __construct()
    {
        // タブ一覧を登録
        $this->tabs = [
            'about'    => new AuroraDesignBlocks_Admin_About(),
            'enable' => new AuroraDesignBlocks_Admin_Enable(),
            'related posts' => new AuroraDesignBlocks_Admin_RelatedPosts(), // ★追加
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




class AuroraDesignBlocks_Admin_About
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


//****************************************************************
//有効化／無効化設定タブ
//****************************************************************
class AuroraDesignBlocks_Admin_Enable
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

//****************************************************************
//関連記事設定タブ
//****************************************************************
class AuroraDesignBlocks_Admin_RelatedPosts
{
    private $sections = [];

    public function __construct()
    {
        // 設定セクションをまとめて登録
        $this->sections = [
            new Aurora_RelatedPosts_Setting_Enable(),
            new Aurora_RelatedPosts_Setting_Count(),
            new Aurora_RelatedPosts_Setting_ShowThumbnail(), // 追加
            // 追加オプションが増えればここに追加するだけ
            // new Aurora_RelatedPosts_Setting_Xxx(),
        ];
    }

    public function get_label()
    {
        return __('Links Rebuild', 'aurora-design-blocks');
    }

    public function render_page()
    {
        // ▼ 先に POST 処理
        $this->handle_actions();

        // ▼ 画面レンダリング
        $this->render_rebuild_form();
        $this->render_settings_form();
    }

    /**
     * ▼ POST（リビルド＋設定保存）を一括処理
     */
    private function handle_actions()
    {
        // リビルド
        if (
            isset($_POST['aurora_rebuild_links']) &&
            check_admin_referer('aurora_rebuild_links_action')
        ) {
            $this->execute_rebuild();
        }

        // 設定保存
        if (
            isset($_POST['aurora_related_posts_settings_save']) &&
            check_admin_referer('aurora_related_posts_settings_action')
        ) {
            $this->save_settings();
        }
    }

    /**
     * ▼ 設定保存
     */
    private function save_settings()
    {
        foreach ($this->sections as $section) {
            $section->save();
        }

        echo '<div class="notice notice-success"><p>'
            . esc_html__('Settings saved.', 'aurora-design-blocks')
            . '</p></div>';
    }

    /**
     * ▼ リビルドフォーム
     */
    private function render_rebuild_form()
    {
    ?>
        <h2><?php echo esc_html__('Rebuild Internal Link Data', 'aurora-design-blocks'); ?></h2>
        <p>
            <?php echo esc_html__('This process scans all posts and regenerates the internal link relationship table.', 'aurora-design-blocks'); ?>
        </p>

        <form method="post">
            <?php wp_nonce_field('aurora_rebuild_links_action'); ?>
            <?php submit_button(__('Run Rebuild', 'aurora-design-blocks'), 'primary', 'aurora_rebuild_links'); ?>
        </form>
    <?php
    }

    /**
     * ▼ 設定フォーム（セクションをループ）
     */
    private function render_settings_form()
    {
    ?>
        <h2><?php echo esc_html__('Related Posts Settings', 'aurora-design-blocks'); ?></h2>

        <form method="post">
            <?php wp_nonce_field('aurora_related_posts_settings_action'); ?>

            <?php foreach ($this->sections as $section): ?>
                <div class="aurora-settings-section">
                    <?php $section->render(); ?>
                </div>
                <br>
            <?php endforeach; ?>

            <?php submit_button(
                __('Save Settings', 'aurora-design-blocks'),
                'secondary',
                'aurora_related_posts_settings_save'
            ); ?>
        </form>
    <?php
    }

    /**
     * ▼ リビルド処理
     */
    private function execute_rebuild()
    {
        global $wpdb;

        $db        = new AuroraDesignBlocks_RelatedPosts_DBManager($wpdb);
        $analyzer  = new AuroraDesignBlocks_RelatedPosts_LinkAnalyzer($db);
        $rebuilder = new AuroraDesignBlocks_RelatedPosts_BatchRebuilder($db, $analyzer);

        $rebuilder->rebuild_all();

        echo '<div class="notice notice-success"><p>'
            . esc_html__('Rebuild completed successfully.', 'aurora-design-blocks')
            . '</p></div>';
    }
}



class Aurora_RelatedPosts_Setting_Enable
{
    public function render()
    {
    ?>
        <label>
            <input type="checkbox"
                name="aurora_related_posts_enable"
                value="1"
                <?php checked(get_option('aurora_related_posts_enable'), '1'); ?>>
            <?php echo esc_html__('Display related posts at the bottom of all single posts', 'aurora-design-blocks'); ?>
        </label>
    <?php
    }

    public function save()
    {
        $value = isset($_POST['aurora_related_posts_enable']) ? '1' : '0';
        update_option('aurora_related_posts_enable', $value);
    }
}




class Aurora_RelatedPosts_Setting_Count
{
    public function render()
    {
        $current = get_option('aurora_related_posts_count', 5);
    ?>
        <label>
            <?php echo esc_html__('Number of related posts to display', 'aurora-design-blocks'); ?>:
            <input type="number"
                name="aurora_related_posts_count"
                value="<?php echo esc_attr($current); ?>"
                min="1" max="100" style="width: 60px;">
        </label>
    <?php
    }

    public function save()
    {
        $val = isset($_POST['aurora_related_posts_count'])
            ? intval($_POST['aurora_related_posts_count'])
            : 5;

        $val = max(1, min(100, $val));

        update_option('aurora_related_posts_count', $val);
    }
}



class Aurora_RelatedPosts_Setting_ShowThumbnail
{
    public function render()
    {
        $current = get_option('aurora_related_posts_show_thumbnail', '1');
    ?>
        <label>
            <input type="checkbox"
                name="aurora_related_posts_show_thumbnail"
                value="1"
                <?php checked($current, '1'); ?>>
            <?php echo esc_html__('Display thumbnails in related posts', 'aurora-design-blocks'); ?>
        </label>
<?php
    }

    public function save()
    {
        $value = isset($_POST['aurora_related_posts_show_thumbnail']) ? '1' : '0';
        update_option('aurora_related_posts_show_thumbnail', $value);
    }
}
