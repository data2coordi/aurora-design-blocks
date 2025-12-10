<?php
if (! defined('ABSPATH')) exit;

class AuroraDesignBlocks_AdminPage_About
{
    /**
     * [新規メソッド] WordPressのフック経由で自身を AuroraDesignBlocks_AdminTabs に登録する
     */
    public static function register_hooks()
    {
        add_action('admin_init', function () {
            $tabs_manager = AuroraDesignBlocks_AdminTop::get_instance()->tabs;
            $tabs_manager->add_tab('about', self::class);
        }, 1);
    }

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


AuroraDesignBlocks_AdminPage_About::register_hooks();
