<?php

if (! defined('ABSPATH')) exit;

class AuroraDesignBlocks_AdminPage_FeatureFlags
{
    /**
     * [新規メソッド] WordPressのフック経由で自身を AuroraDesignBlocks_AdminTabs に登録する
     */
    public static function register_hooks()
    {
        add_action('admin_init', function () {
            $tabs_manager = AuroraDesignBlocks_AdminTop::get_instance()->tabs;
            $tabs_manager->add_tab('settings', self::class);
        }, 1);
    }

    public function __construct()
    {
        // 設定を登録
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function get_label()
    {
        return __('Enable / Disable Settings', 'aurora-design-blocks');
    }
    // ... （中略：register_settings, sanitize, render_page メソッドは変更なし）
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

AuroraDesignBlocks_AdminPage_FeatureFlags::register_hooks();
