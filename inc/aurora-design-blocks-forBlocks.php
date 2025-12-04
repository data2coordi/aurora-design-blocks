<?php
if (! defined('ABSPATH')) exit;



/********************************************************************/
/*ブロックアイテムの読み込みs*/
/********************************************************************/


// ブロック登録処理をカスタマイズして実行
function AuroraDesignBlocks_register_custom_blocks()
{
	$blocks = glob(AURORA_DESIGN_BLOCKS_PATH . 'blocks/*', GLOB_ONLYDIR);

	// 動的レンダリングコールバックが必要なブロックと、そのコールバック関数を定義
	$dynamic_blocks_callbacks = [
		'related-posts' => 'adb_render_related_posts_block_html', // あなたのブロック
		// 他の動的ブロックがあればここに追加
	];

	foreach ($blocks as $block_dir) {
		if (file_exists($block_dir . '/block.json')) {

			$args = [];
			$block_slug = basename($block_dir); // 例: 'related-posts'

			// ブロックのスラッグが動的ブロックリストにあるかチェック
			if (isset($dynamic_blocks_callbacks[$block_slug])) {
				// コールバックを引数に追加
				$args['render_callback'] = $dynamic_blocks_callbacks[$block_slug];
			}

			// カスタム引数（コールバックなど）を含めてブロックを登録
			register_block_type($block_dir, $args);
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


/**
 * クラス名：AuroraDesignBlocksPreDetermineBlocksCss
 * 目的：ページ内で使用されているブロックを render_block フィルターで検出し、
 * 必要なスタイルを遅延ロードリストに追加する。
 */
class AuroraDesignBlocksPreDetermineBlocksCss
{
	/**
	 * ブロックごとのフロントCSS設定
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
		// 1. まず全ブロックを登録 (wp_register_style)
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

		// ウィジェット（サイドバー）のデータを一度だけ取得しておく
		$all_widget_blocks = get_option('widget_block');

		// 各ブロックごとに判定
		foreach (self::$blocks as $blockName => $styles) {
			$found = false;

			// A. メインコンテンツ内のチェック (既存のロジック)
			if (has_block($blockName)) {
				$found = true;
			}

			// B. サイドバー（ウィジェット）内のチェック (Aで見つからなかった場合のみ)
			if (!$found && !empty($all_widget_blocks) && is_array($all_widget_blocks)) {
				foreach ($all_widget_blocks as $widget_data) {
					// ウィジェットのコンテンツ内にブロック名が含まれているかチェック
					if (isset($widget_data['content']) && has_block($blockName, $widget_data['content'])) {
						$found = true;
						break; // 見つかったらこのブロックの検索は終了
					}
				}
			}

			// 見つかった場合の処理
			if ($found) {
				// フロント用に登録
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

// 初期化フック ('wp' アクションに戻しました)
add_action('wp', ['AuroraDesignBlocksPreDetermineBlocksCss', 'init']);
/********************************************************************/
/*ブロックアイテムのフロント用のCSSの登録e*/
/********************************************************************/

/************************************************************/
/*ブロックアイテムのjsのロード s*/
/************************************************************/
class AuroraDesignBlocksPreDetermineJsAssets
{
	// スクリプト登録が必要なブロックのリスト
	private static $script_blocks = [
		'aurora-design-blocks/tab-block',
		'aurora-design-blocks/slider-block',
		// 他のスクリプトを必要とするブロックもここに追加
	];

	/**
	 * ブロックがページ（メインコンテンツまたはウィジェット）に存在するかをチェックする
	 * @param string $blockName 
	 * @return bool
	 */
	private static function block_is_present(string $blockName): bool
	{
		// A. メインコンテンツ内のチェック
		if (has_block($blockName)) {
			return true;
		}

		// B. サイドバー（ウィジェット）内のチェック
		$all_widget_blocks = get_option('widget_block');

		if (!empty($all_widget_blocks) && is_array($all_widget_blocks)) {
			foreach ($all_widget_blocks as $widget_data) {
				// ウィジェットのコンテンツ内にブロック名が含まれているかチェック
				if (isset($widget_data['content']) && has_block($blockName, $widget_data['content'])) {
					return true;
				}
			}
		}

		return false;
	}

	public static function init()
	{
		// 投稿のコンテンツを取得（この行は残すものの、直接 $post を使わず関数化）
		global $post;

		$scripts = [];

		// 1. タブブロックが存在する場合のみ登録
		if (self::block_is_present('aurora-design-blocks/tab-block')) {
			$scripts['aurora-design-blocks-tab-block-script'] = [
				'path' => 'blocks/tab-block/build/frontend.js',
				'deps' => [],
			];
		}

		// 2. スライダーブロックが存在する場合のみ登録
		if (self::block_is_present('aurora-design-blocks/slider-block')) {
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

// 初期化フック ('wp' アクションで実行することを推奨)
add_action('wp', ['AuroraDesignBlocksPreDetermineJsAssets', 'init']);
/************************************************************/
/*ブロックアイテムのjsのロード s*/
/************************************************************/
