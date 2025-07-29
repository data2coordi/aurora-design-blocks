<?php

/**
 * 初回だけ theme_mod の値を option に移行する。
 */
function aurora_design_blocks_migrate_theme_mods_to_options_once()
{
    // 管理画面でのみ実行
    if (! is_admin()) {
        return;
    }

    // すでに移行済みかチェック
    if (get_option('aurora_design_blocks_options_migrated')) {
        return;
    }

    $target_options = [
        'auroraDesignBlocks_ga_trackingCode',
        'auroraDesignBlocks_gtm_trackingCode',
        'auroraDesignBlocks_gtm_noscriptCode',
        'auroraDesignBlocks_adsense_code',
    ];

    foreach ($target_options as $option_name) {
        $option_value = get_option($option_name, '');
        if ($option_value === '') {
            $theme_mod_value = get_theme_mod($option_name);
            if ($theme_mod_value !== null && $theme_mod_value !== '') {
                update_option($option_name, $theme_mod_value);
            }
        }
    }

    // フラグを保存して2回目以降はスキップ
    update_option('aurora_design_blocks_options_migrated', true);
}
add_action('admin_init', 'aurora_design_blocks_migrate_theme_mods_to_options_once');
