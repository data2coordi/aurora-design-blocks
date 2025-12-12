<?php
if (! defined('ABSPATH')) exit;

/**
 * Plugin Name: Aurora Design Blocks
 * Description: Multi-functional plugin for GA4, GTM, AdSense, OGP, and automated Table of Contents (TOC), generally essential for blogs.
 * Version: 0.0.8
 * Author: Yurika Toshida at Aurora Lab
 * Text Domain: aurora-design-blocks
 * Domain Path: /languages
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('AURORA_DESIGN_BLOCKS_VERSION')) {
    // Replace the version number of the theme on each release.
    define('AURORA_DESIGN_BLOCKS_VERSION', '0.0.8');
}



if (!defined('ABSPATH')) {
    exit; // 直接アクセスを防ぐ
}

define('AURORA_DESIGN_BLOCKS_PATH', plugin_dir_path(__FILE__));
define('AURORA_DESIGN_BLOCKS_URL', plugin_dir_url(__FILE__));
define('ADB_PLUGIN_FILE', __FILE__);

require AURORA_DESIGN_BLOCKS_PATH . '/inc/admin.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/admin-front.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/admin-page-about.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/admin-page-featureFlags.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/admin-page-createSlug.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-outerAssets.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-customizer.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-auroraDesignBlocks.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks_helper.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-createSlug.php';



/**
 * 指定したファイル群を存在チェック後に読み込む
 *
 * @param string[] $files ファイルパスの配列（相対パス）
 * @param string $base_path ベースディレクトリパス
 */
function auroraDesignBlocks_load_files_if_exist(array $files, string $base_path)
{
    foreach ($files as $file) {
        $full_path = $base_path . $file;
        if (file_exists($full_path)) {
            require $full_path;
        }
    }
}

// 使用例
$auroraDesignBlocks_files_to_load = [
    '/aurora-design-blocks-pro.php',
    '/inc/aurora-design-blocks-textDomain.php',
    '/inc/admin-page-relatedPosts.php',
    '/inc/aurora-design-blocks-relatedPosts.php'
];

auroraDesignBlocks_load_files_if_exist($auroraDesignBlocks_files_to_load, AURORA_DESIGN_BLOCKS_PATH);


// 条件付きで他のファイルをロード e