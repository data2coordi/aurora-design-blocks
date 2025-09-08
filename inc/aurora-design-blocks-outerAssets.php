<?php

/**
 * AuroraDesignBlocks functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package AuroraDesignBlocks
 */


//## スタイルシート、JSファイルの追加 _s //////////////////////////////////////////////////////
/**
 * Enqueue scripts and styles.
 */
/********************************************************* */
/* cssファイル s  **********************************/
/********************************************************* */


class AuroraDesignBlocksDeferCss
{
	private static $deferred_styles = [];

	/**
	 * 初期化メソッド
	 */
	public static function init()
	{
		add_filter('style_loader_tag', [__CLASS__, 'defer_css'], 10, 2);
	}

	/**
	 * 遅延対象のスタイルを追加する
	 * @param array $styles 追加するスタイルのハンドル名の配列
	 */
	public static function add_deferred_styles(array $styles)
	{
		self::$deferred_styles = array_merge(self::$deferred_styles, $styles);
		self::$deferred_styles = array_unique(self::$deferred_styles); // 重複を排除
	}

	/**
	 * CSSの遅延読み込み処理
	 * @param string $tag    スタイルタグ
	 * @param string $handle スタイルのハンドル名
	 * @return string 修正後のタグ
	 */
	public static function defer_css($tag, $handle)
	{
		if (in_array($handle, self::$deferred_styles, true)) {
			return str_replace("rel='stylesheet'", "rel='stylesheet' media='print' onload=\"this.media='all'\"", $tag);
		}
		return $tag;
	}
}

// フィルタの登録を実行
AuroraDesignBlocksDeferCss::init();


class AuroraDesignBlocksRegStyles
{
	protected static $styles = []; // 各サブクラスで独自の配列を持つ

	public static function add_styles(array $styles)
	{
		static::$styles = array_merge(static::$styles, $styles);
	}

	public static function enqueue_styles()
	{
		foreach (static::$styles as $handle => $path) {
			wp_enqueue_style($handle, plugin_dir_url(dirname(__FILE__))  . $path, [], _AuroraDesignBlocks_S_VERSION);
		}
	}
}

// フロントエンド用のスタイル管理クラス
class AuroraDesignBlocksFrontendStyles extends AuroraDesignBlocksRegStyles
{
	protected static $styles = []; // フロントエンド専用の配列

	public static function init()
	{
		add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_styles'], 20);
	}
}

// エディタ用のスタイル管理クラス
class AuroraDesignBlocksEditorStyles extends AuroraDesignBlocksRegStyles
{
	protected static $styles = []; // エディタ専用の配列

	public static function init()
	{
		add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_styles']);
	}
}

// 各クラスの初期化
AuroraDesignBlocksFrontendStyles::init();
AuroraDesignBlocksEditorStyles::init();

// フロントエンド用のスタイル登録 利用例
/*
AuroraDesignBlocksFrontendStyles::add_styles([
    'custom-pattern' => '/css/custom-pattern.css',
]);

// エディタ用のスタイル登録 利用例
AuroraDesignBlocksEditorStyles::add_styles([
    'editor-style' => '/css/editor-style.css',
]);
*/
/********************************************************* */
/* cssファイル e  **********************************/
/********************************************************* */



/********************************************************* */
/* jsファイル s***********************************************/
/********************************************************* */


class AuroraDesignBlocksRegScripts
{
	/**
	 * スクリプトを登録する共通処理（サブクラスで配列を管理）
	 */
	protected static function enqueue_scripts($scripts)
	{
		foreach ($scripts as $handle => $data) {
			$path = $data['path'];
			$deps = isset($data['deps']) ? $data['deps'] : [];

			wp_enqueue_script($handle,  plugin_dir_url(dirname(__FILE__)) . $path, $deps, _AuroraDesignBlocks_S_VERSION, true);
		}
	}
}

/**
 * フロントエンド用のスクリプト管理
 */
class AuroraDesignBlocksFrontendScripts extends AuroraDesignBlocksRegScripts
{
	private static $scripts = [];

	public static function init()
	{
		add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_scripts']);
	}

	public static function add_scripts(array $scripts)
	{
		self::$scripts = array_merge(self::$scripts, $scripts);
	}

	public static function enqueue_frontend_scripts()
	{
		parent::enqueue_scripts(self::$scripts);
	}
}
AuroraDesignBlocksFrontendScripts::init();

/**
 * ブロックエディタ用のスクリプト管理
 */
class AuroraDesignBlocksEditorScripts extends AuroraDesignBlocksRegScripts
{
	private static $scripts = [];

	public static function init()
	{
		add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_editor_scripts']);
	}

	public static function add_scripts(array $scripts)
	{
		self::$scripts = array_merge(self::$scripts, $scripts);
	}

	public static function enqueue_editor_scripts()
	{
		parent::enqueue_scripts(self::$scripts);
	}
}

// 初期化
AuroraDesignBlocksEditorScripts::init();


class AuroraDesignBlocksDeferJs
{
	private static $deferred_scripts = [];

	/**
	 * 初期化メソッド
	 */
	public static function init()
	{
		add_filter('script_loader_tag', [__CLASS__, 'defer_js'], 10, 2);
	}

	/**
	 * 遅延対象のスクリプトを追加する
	 * @param array $scripts 追加するスクリプトのハンドル名の配列
	 */
	public static function add_deferred_scripts(array $scripts)
	{
		self::$deferred_scripts = array_merge(self::$deferred_scripts, $scripts);
		self::$deferred_scripts = array_unique(self::$deferred_scripts); // 重複を排除
	}

	/**
	 * JSの遅延読み込み処理
	 * @param string $tag    スクリプトタグ
	 * @param string $handle スクリプトのハンドル名
	 * @return string 修正後のタグ
	 */
	public static function defer_js($tag, $handle)
	{
		if (in_array($handle, self::$deferred_scripts, true)) {
			return str_replace(' src', ' defer src', $tag);
		}
		return $tag;
	}
}

// フィルタの登録を実行
AuroraDesignBlocksDeferJs::init();




class AuroraDesignBlocksMoveScripts
{
	private static $scripts = [];

	/**
	 * 初期化メソッド
	 */
	public static function init()
	{
		add_action('wp_enqueue_scripts', [__CLASS__, 'move_scripts_to_footer'], 999);
	}

	/**
	 * フッターに移動するスクリプトを登録
	 * @param array $scripts ['handle' => 'パス'] の配列
	 */
	public static function add_scripts(array $scripts)
	{
		self::$scripts = array_merge(self::$scripts, $scripts);
	}

	/**
	 * 指定したスクリプトをフッターに移動
	 */
	public static function move_scripts_to_footer()
	{
		if (!is_admin()) {
			foreach (self::$scripts as $handle => $path) {

				wp_dequeue_script($handle);
				wp_deregister_script($handle);
				wp_register_script($handle, $path, [], _AuroraDesignBlocks_S_VERSION, true);
				wp_enqueue_script($handle);
			}
		}
	}
}

// 初期化処理
AuroraDesignBlocksMoveScripts::init();
/********************************************************* */
/* jsファイル e***********************************************/
/********************************************************* */
