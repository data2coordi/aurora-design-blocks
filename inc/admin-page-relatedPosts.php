<?php

//****************************************************************
//関連記事設定タブ (AuroraDesignBlocks_Admin_RelatedPosts)
//****************************************************************
class AuroraDesignBlocks_Admin_RelatedPosts
{
    private $sections = [];

    /**
     * [新規メソッド] WordPressのフック経由で自身を AuroraDesignBlocks_AdminTabs に登録する
     */
    public static function register_hooks()
    {
        // 優先度1でadmin_initフックに登録し、AdminTopインスタンスのタブマネージャーに自身を追加
        add_action('admin_init', function () {
            // AdminTopクラスのインスタンスを取得し、その中のtabsプロパティにアクセス
            $tabs_manager = AuroraDesignBlocks_AdminTop::get_instance()->tabs;
            // スラッグ 'related_posts' で自身 (AuroraDesignBlocks_Admin_RelatedPosts) を登録
            $tabs_manager->add_tab('related_posts', self::class);
        }, 1);
    }

    public function __construct()
    {
        // 設定セクションをまとめて登録
        $this->sections = [
            new AuroraDesignBlocks_RelatedPosts_Setting_Enable(),
            new AuroraDesignBlocks_RelatedPosts_Setting_Count(),
            new AuroraDesignBlocks_RelatedPosts_Setting_ShowThumbnail(),
        ];
    }

    public function get_label()
    {
        // タブのラベルを返す
        return __('Related Posts / Link Rebuild', 'aurora-design-blocks');
    }
    
    // ... (render_page, handle_actions, save_settings, render_rebuild_form, render_settings_form, execute_rebuild メソッドは変更なし)
    // ... (既存のコードをそのまま使用)

    /**
     * メインページをレンダリング
     */
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

        // DBManager, LinkAnalyzer, BatchRebuilderなどのクラスは別途定義されているものと仮定
        $db        = new AuroraDesignBlocks_RelatedPosts_DBManager($wpdb);
        $analyzer  = new AuroraDesignBlocks_RelatedPosts_LinkAnalyzer($db);
        $rebuilder = new AuroraDesignBlocks_RelatedPosts_BatchRebuilder($db, $analyzer);

        $rebuilder->rebuild_all();

        echo '<div class="notice notice-success"><p>'
            . esc_html__('Rebuild completed successfully.', 'aurora-design-blocks')
            . '</p></div>';
    }
}
// ... (AuroraDesignBlocks_RelatedPosts_Setting_Enable, AuroraDesignBlocks_RelatedPosts_Setting_Count, AuroraDesignBlocks_RelatedPosts_Setting_ShowThumbnail クラスは変更なし)
// 
AuroraDesignBlocks_Admin_RelatedPosts::register_hooks();



class AuroraDesignBlocks_RelatedPosts_Setting_Enable
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
    // ★追加
    public static function is_enabled(): bool
    {
        return get_option('aurora_related_posts_enable', '0') === '1';
    }
}




class AuroraDesignBlocks_RelatedPosts_Setting_Count
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

    // ★追加
    public static function get_count(): int
    {
        return intval(get_option('aurora_related_posts_count', 5));
    }
}



class AuroraDesignBlocks_RelatedPosts_Setting_ShowThumbnail
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

    // ★追加
    public static function is_enabled(): bool
    {
        return get_option('aurora_related_posts_show_thumbnail', '0') === '1';
    }
}
