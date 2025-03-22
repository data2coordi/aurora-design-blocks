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

//Font Awesome 
function add_fontawesome_button_to_toolbar()
{
	//js 読み込み
	$scripts = [
		'integlight-gfontawesome' =>  ['path' => '/blocks/gfontawesome/build/index.js', 'deps' => ['wp-blocks', 'wp-i18n', 'wp-element',  'wp-rich-text']],
	];
	InteglightEditorScripts::add_scripts($scripts);
	$deferredScripts = ['integlight-gfontawesome'];
	InteglightDeferJs::add_deferred_scripts($deferredScripts);
}
add_fontawesome_button_to_toolbar();

//右寄せサンプルツールバー
/*
function add_right_align_button_to_toolbar()
{
	wp_enqueue_script(
		'custom-right-align-button',
		get_template_directory_uri() . '/blocks/right-align-button/build/index.js', // ビルドされたファイルを読み込む
		array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
		'1.0',
		true
	);
}

add_action('enqueue_block_editor_assets', 'add_right_align_button_to_toolbar');
*/

/********************************************************************/
/*ブロックアイテムの読み込みe*/
/********************************************************************/

/********************************************************************/
/*font awesome 用フィルター置換*/
/********************************************************************/

function integlight_replace_fontawesome_icons($content)
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
add_filter('the_content', 'integlight_replace_fontawesome_icons', 10);

/********************************************************************/
/*font awesome 用のショートコードe*/
/********************************************************************/




/********************************************************************/
/*ブロックの国際化対応s*/
/********************************************************************/

function integlight_enqueue_block_assets()
{

	wp_set_script_translations(
		'integlight-custom-cover-block-editor-script',
		'integlight',
		get_template_directory() . '/languages'
	);
	wp_set_script_translations(
		'integlight-gfontawesome-block-editor-script',
		'integlight',
		get_template_directory() . '/languages'
	);

	/*
	wp_set_script_translations(
		'integlight-hello-world-block-editor-script',
		'integlight',
		get_template_directory() . '/languages'
	);
	*/

	wp_set_script_translations(
		'integlight-slider-block-block-editor-script',
		'integlight',
		get_template_directory() . '/languages'
	);


	wp_set_script_translations(
		'integlight-speech-bubble-editor-script', // ハンドル名を適切に設定
		'integlight',
		get_template_directory() . '/languages'
	);

	wp_set_script_translations(
		'integlight-tab-block-editor-script', // ハンドル名を適切に設定
		'integlight',
		get_template_directory() . '/languages'
	);


	wp_set_script_translations(
		'integlight-text-flow-animation-editor-script',
		'integlight',
		get_template_directory() . '/languages'
	);
}
add_action('enqueue_block_editor_assets', 'integlight_enqueue_block_assets');




/********************************************************************/
/*ブロックの国際化対応e*/
/********************************************************************/
/********************************************************************/
/*PF最適化 s*/
/********************************************************************/


// フッターに移動するスクリプトを登録


$footerScripts = [
	'integlight-tab-block-script'   => get_template_directory_uri() . '/blocks/tab-block/src/frontend.js',
	'integlight-slider-block-script'   => get_template_directory_uri() . '/blocks/slider-block/src/frontend.js',

];
InteglightMoveScripts::add_scripts($footerScripts);


$deferredScripts = [
	'integlight-tab-block-script',
	'integlight-slider-block-script'

];
InteglightDeferJs::add_deferred_scripts($deferredScripts);

/* レンダリングブロック、layout計算増加の防止のためのチューニング e*/


/********************************************************************/
/*PF最適化 e*/
/********************************************************************/
