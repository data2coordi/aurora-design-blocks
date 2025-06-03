<?php



/********************************************************************/
/*ブロックアイテムの読み込みs*/
/********************************************************************/

// ブロック登録処理を追加
function AuroraDesignBlocks_register_custom_blocks()
{
	$blocks = glob(AURORA_DESIGN_BLOCKS_PATH . 'blocks/*', GLOB_ONLYDIR);

	foreach ($blocks as $block) {
		if (file_exists($block . '/block.json')) {
			register_block_type($block);
		}
	}
}
add_action('init', 'AuroraDesignBlocks_register_custom_blocks');
/********************************************************************/
/*ブロックアイテムの読み込みe*/
/********************************************************************/





/********************************************************************/
/*ブロックの国際化対応s*/
/********************************************************************/

function auroraDesignBlocks_enqueue_block_assets()
{

	wp_set_script_translations(
		'aurora-design-blocks-custom-cover-editor-script',
		'aurora-design-blocks',
		AURORA_DESIGN_BLOCKS_PATH . 'languages'
	);


	wp_set_script_translations(
		'aurora-design-blocks-slider-block-editor-script',
		'aurora-design-blocks',
		AURORA_DESIGN_BLOCKS_PATH . 'languages'
	);


	wp_set_script_translations(
		'aurora-design-blocks-speech-bubble-editor-script', // ハンドル名を適切に設定
		'aurora-design-blocks',
		AURORA_DESIGN_BLOCKS_PATH . 'languages'
	);

	wp_set_script_translations(
		'aurora-design-blocks-tab-block-editor-script', // ハンドル名を適切に設定
		'aurora-design-blocks',
		AURORA_DESIGN_BLOCKS_PATH . 'languages'
	);


	wp_set_script_translations(
		'aurora-design-blocks-text-flow-animation-editor-script',
		'aurora-design-blocks',
		AURORA_DESIGN_BLOCKS_PATH . 'languages'
	);

	wp_set_script_translations(
		'aurora-design-blocks-cta-block-editor-script',
		'aurora-design-blocks',
		AURORA_DESIGN_BLOCKS_PATH . 'languages'
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
	'aurora-design-blocks-tab-block-script'   => AURORA_DESIGN_BLOCKS_URL . 'blocks/tab-block/build/frontend.js',
	'aurora-design-blocks-slider-block-script'   => AURORA_DESIGN_BLOCKS_URL   . 'blocks/slider-block/build/frontend.js',

];
AuroraDesignBlocksMoveScripts::add_scripts($footerScripts);


$deferredScripts = [
	'aurora-design-blocks-tab-block-script',
	'aurora-design-blocks-slider-block-script'

];
AuroraDesignBlocksDeferJs::add_deferred_scripts($deferredScripts);

/* レンダリングブロック、layout計算増加の防止のためのチューニング e*/


/********************************************************************/
/*PF最適化 e*/
/********************************************************************/
