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
//ブロック用




//js用
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

	wp_set_script_translations(
		'aurora-design-blocks-frame-line-editor-script',
		'aurora-design-blocks',
		AURORA_DESIGN_BLOCKS_PATH . 'languages'
	);


	wp_set_script_translations(
		'AuroraDesignBlocks-gfontawesome',
		'aurora-design-blocks',
		AURORA_DESIGN_BLOCKS_PATH . 'languages'
	);
}
add_action('enqueue_block_editor_assets', 'auroraDesignBlocks_enqueue_block_assets');




/********************************************************************/
/*ブロックの国際化対応e*/
/********************************************************************/




class AuroraDesignBlocksPreDetermineCssBlocks
{
	/**
	 * ブロックごとのフロントCSS設定
	 * 'block-name' => ['handle' => 'css-path']
	 */
	private static $blocks = [
		'aurora-design-blocks/cta-block' => [
			'aurora-design-blocks-cta-block-style' => 'blocks/cta-block/build/style-index.css',
		],
		'aurora-design-blocks/custom-cover-block' => [
			'aurora-design-blocks-custom-cover-style' => 'blocks/custom-cover/build/style-index.css',
		],
		// 他ブロックもここに追加可能
	];

	private static $deferredStyles = [];

	/**
	 * フロント用初期化
	 */
	public static function init()
	{
		// まず全ブロックを登録
		foreach (self::$blocks as $blockName => $styles) {
			foreach ($styles as $handle => $path) {
				wp_register_style(
					$handle,
					AURORA_DESIGN_BLOCKS_URL . $path,
					[],
					'1.0.0'
				);
			}
		}

		// 各ブロックごとに判定してフロントでenqueue
		foreach (self::$blocks as $blockName => $styles) {
			if (is_singular() && has_block($blockName)) {
				// フロント用に登録（enqueueではなく add_styles に登録）
				AuroraDesignBlocksFrontendStyles::add_styles($styles);

				// 遅延対象に追加
				self::$deferredStyles = array_merge(self::$deferredStyles, array_keys($styles));
			}
		}

		if (!empty(self::$deferredStyles)) {
			AuroraDesignBlocksDeferCss::add_deferred_styles(self::$deferredStyles);
		}
	}
}

// 初期化フック
add_action('wp', ['AuroraDesignBlocksPreDetermineCssBlocks', 'init']);
