<?php

/********************************************************************/
/*ブロックアイテムの読み込みs*/
/********************************************************************/

function register_theme_blocks()
{
	$blocks = glob(get_template_directory() . '/blocks/*', GLOB_ONLYDIR);
	foreach ($blocks as $block) {
		if (file_exists($block . '/block.json')) {
			register_block_type($block);
		}
	}
}
add_action('init', 'register_theme_blocks');



/********************************************************************/
/*ブロックアイテムの読み込みe*/
/********************************************************************/

/********************************************************************/
/*font awesome 用フィルター置換*/
/********************************************************************/

function auroraDesignBlocks_replace_fontawesome_icons($content)
{
	return preg_replace_callback(
		'/\[fontawesome icon=([a-z0-9-]+)\]/i',
		function ($matches) {
			$icon = $matches[1];
			if (empty($icon)) {
				return '';
			}
			return '<i class="fas ' . esc_attr($icon) . '"></i>';
		},
		$content
	);
}
add_filter('the_content', 'auroraDesignBlocks_replace_fontawesome_icons', 10);

/********************************************************************/
/*font awesome 用のショートコードe*/
/********************************************************************/




/********************************************************************/
/*ブロックの国際化対応s*/
/********************************************************************/

function auroraDesignBlocks_enqueue_block_assets()
{

	wp_set_script_translations(
		'aurora-design-blocks-custom-cover-block-editor-script',
		'aurora-design-blocks',
		get_template_directory() . '/languages'
	);
	wp_set_script_translations(
		'aurora-design-blocks-gfontawesome-block-editor-script',
		'aurora-design-blocks',
		get_template_directory() . '/languages'
	);

	/*
	wp_set_script_translations(
		'aurora-design-blocks-hello-world-block-editor-script',
		'aurora-design-blocks',
		get_template_directory() . '/languages'
	);
	*/

	wp_set_script_translations(
		'aurora-design-blocks-slider-block-block-editor-script',
		'aurora-design-blocks',
		get_template_directory() . '/languages'
	);


	wp_set_script_translations(
		'aurora-design-blocks-speech-bubble-editor-script', // ハンドル名を適切に設定
		'aurora-design-blocks',
		get_template_directory() . '/languages'
	);

	wp_set_script_translations(
		'aurora-design-blocks-tab-block-editor-script', // ハンドル名を適切に設定
		'aurora-design-blocks',
		get_template_directory() . '/languages'
	);


	wp_set_script_translations(
		'aurora-design-blocks-text-flow-animation-editor-script',
		'aurora-design-blocks',
		get_template_directory() . '/languages'
	);
}
add_action('enqueue_block_editor_assets', 'auroraDesignBlocks_enqueue_block_assets');




/********************************************************************/
/*ブロックの国際化対応e*/
/********************************************************************/
/********************************************************************/
/*PF最適化 s*/
/********************************************************************/


// フッターに移動するスクリプトを登録


$footerScripts = [
	'aurora-design-blocks-tab-block-script'   => get_template_directory_uri() . '/blocks/tab-block/src/frontend.js',
	'aurora-design-blocks-slider-block-script'   => get_template_directory_uri() . '/blocks/slider-block/src/frontend.js',

];
//AuroraDesignBlocksMoveScripts::add_scripts($footerScripts);


$deferredScripts = [
	'aurora-design-blocks-tab-block-script',
	'aurora-design-blocks-slider-block-script'

];
//AuroraDesignBlocksDeferJs::add_deferred_scripts($deferredScripts);

/* レンダリングブロック、layout計算増加の防止のためのチューニング e*/


/********************************************************************/
/*PF最適化 e*/
/********************************************************************/
