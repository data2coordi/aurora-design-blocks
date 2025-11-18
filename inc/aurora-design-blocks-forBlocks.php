<?php
if (! defined('ABSPATH')) exit;



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


/********************************************************************/
/*ブロックアイテムのフロント用のCSSの登録s*/
/********************************************************************/



class AuroraDesignBlocksPreDetermineBlocksCss
{
	/**
	 * ブロックごとのフロントCSS設定
	 * 'block-name' => ['handle' => 'css-path']
	 */
	private static $blocks = [
		'aurora-design-blocks/cta-block' => [
			'aurora-design-blocks-cta-block-style' => 'blocks/cta-block/build/style-index.css',
		],
		'aurora-design-blocks/custom-cover' => [
			'aurora-design-blocks-custom-cover-style' => 'blocks/custom-cover/build/style-index.css',
		],
		'aurora-design-blocks/frame-line' => [
			'aurora-design-blocks-frame-line-style' => 'blocks/frame-line/build/style-index.css',
		],
		'aurora-design-blocks/slider-block' => [
			'aurora-design-blocks-slider-block-style' => 'blocks/slider-block/build/style-index.css',
		],
		'aurora-design-blocks/speech-bubble' => [
			'aurora-design-blocks-speech-bubble-style' => 'blocks/speech-bubble/build/style-index.css',
		],
		'aurora-design-blocks/tab-block' => [
			'aurora-design-blocks-tab-block-style' => 'blocks/tab-block/build/style-index.css',
		],
		'aurora-design-blocks/text-flow-animation' => [
			'aurora-design-blocks-text-flow-animation-style' => 'blocks/text-flow-animation/build/style-index.css',
		],
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
add_action('wp', ['AuroraDesignBlocksPreDetermineBlocksCss', 'init']);

/********************************************************************/
/*ブロックアイテムのフロント用のCSSの登録e*/
/********************************************************************/

/************************************************************/
/*ブロックアイテムのjsのロード s*/
/************************************************************/
class AuroraDesignBlocksPreDetermineJsAssets
{

	public static function init()
	{
		// 個別ページかつ該当ブロックが存在する場合のみ処理
		if (is_singular()) {

			// 投稿のコンテンツを取得
			global $post;
			$content = $post ? $post->post_content : '';

			$scripts = [];

			// タブブロックが存在する場合のみ登録
			if (has_block('aurora-design-blocks/tab-block', $post)) {
				$scripts['aurora-design-blocks-tab-block-script'] = [
					'path' => 'blocks/tab-block/build/frontend.js',
					'deps' => [],
				];
			}

			// スライダーブロックが存在する場合のみ登録
			if (has_block('aurora-design-blocks/slider-block', $post)) {
				$scripts['aurora-design-blocks-slider-block-script'] = [
					'path' => 'blocks/slider-block/build/frontend.js',
					'deps' => [],
				];
			}

			if (! empty($scripts)) {
				// 登録
				AuroraDesignBlocksFrontendScripts::add_scripts($scripts);

				// defer 適用
				$deferredScripts = array_keys($scripts);
				AuroraDesignBlocksDeferJs::add_deferred_scripts($deferredScripts);

				/* レンダリングブロック、layout計算増加の防止のためのチューニング */
			}
		}
	}
}

// 初期化処理（ルートで実行）
add_action('wp', ['AuroraDesignBlocksPreDetermineJsAssets', 'init']);

/************************************************************/
/*ブロックアイテムのjsのロード s*/
/************************************************************/
