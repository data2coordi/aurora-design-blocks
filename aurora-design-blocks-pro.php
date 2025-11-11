<?php

require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-auroraDesignBlocks-pro.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-forBlocks.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-awesome.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-pattern.php';
if (!defined('AURORA_DESIGN_BLOCKS_WP_ENVIRONMENT_TYPE') || AURORA_DESIGN_BLOCKS_WP_ENVIRONMENT_TYPE !== 'testing') {
    require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-popularPosts.php';
}

///////////////////////////////////////////
//プラグインの自動更新s///////
///////////////////////////////////////////

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
// ライブラリの読み込み（相対パスは環境に合わせて修正）
require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';

// アップデートチェッカーのインスタンス作成
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://integlight.auroralab-design.com/wp-content/updates/aurora-design-blocks.json', // WebアクセスできるJSONのURL
    __FILE__,   // プラグインのメインファイルの絶対パス（ここでは現在のファイル）
    'aurora-design-blocks' // プラグインの一意のスラッグ（通常はフォルダ名）
);
///////////////////////////////////////////
//プラグインの自動更新e//
///////////////////////////////////////////

///////////////////////////////////////////
//DB用s///////
///////////////////////////////////////////
register_activation_hook(__FILE__, ['AuroraDesignBlocks_PostViewTracker', 'create_views_table']);


///////////////////////////////////////////
//DB用e///////
///////////////////////////////////////////
