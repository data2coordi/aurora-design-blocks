<?php
if (! defined('ABSPATH')) exit;

/**
 * Plugin Name: Aurora Design Blocks
 * Description: Multi-functional plugin for GA4, GTM, AdSense, OGP, and automated Table of Contents (TOC), generally essential for blogs.
 * Version: 2.0.7
 * Author: Yurika Toshida at Aurora Lab
 * Text Domain: aurora-design-blocks
 * Domain Path: /languages
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('AURORA_DESIGN_BLOCKS_VERSION')) {
    // Replace the version number of the theme on each release.
    define('AURORA_DESIGN_BLOCKS_VERSION', '2.0.7');
}



if (!defined('ABSPATH')) {
    exit; // 直接アクセスを防ぐ
}

define('AURORA_DESIGN_BLOCKS_PATH', plugin_dir_path(__FILE__));
define('AURORA_DESIGN_BLOCKS_URL', plugin_dir_url(__FILE__));
define('ADB_PLUGIN_FILE', __FILE__);

require AURORA_DESIGN_BLOCKS_PATH . '/inc/admin.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/admin-front.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-outerAssets.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-customizer.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-auroraDesignBlocks.php';
require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks_helper.php';

if (file_exists(AURORA_DESIGN_BLOCKS_PATH . "/aurora-design-blocks-pro.php")) {
    require AURORA_DESIGN_BLOCKS_PATH . '/aurora-design-blocks-pro.php';
}
if (file_exists(AURORA_DESIGN_BLOCKS_PATH . "/inc/aurora-design-blocks-textDomain.php")) {
    require AURORA_DESIGN_BLOCKS_PATH . '/inc/aurora-design-blocks-textDomain.php';
}
