<?php
class AuroraDesignBlocks_AdminFront_FeatureFlags
{

    public static function get_options()
    {
        $saved = get_option('aurora_design_blocks_options');

        $default = [
            'ogp_enabled' => '1',
            'toc_enabled' => '1',
        ];

        if (! is_array($saved)) {
            return $default;
        }

        // 保存されていないキーも補完
        return array_merge($default, $saved);
    }

    public static function is_enabled($key)
    {
        $options = self::get_options();
        return isset($options[$key]) && $options[$key] === '1';
    }

    public static function ogp()
    {
        return self::is_enabled('ogp_enabled');
    }

    public static function toc()
    {
        return self::is_enabled('toc_enabled');
    }
}



class AuroraDesignBlocks_AdminFront_CreateSlug
{
    // 設定ページで登録したオプション名
    const OPTION_NAME = 'aurora_gemini_ai_options';

    /**
     * DBから保存されたオプション値を取得し、デフォルト値とマージして返す。
     *
     * @return array
     */
    public static function get_options()
    {
        // 1. DBから保存された値を取得
        $saved = get_option(self::OPTION_NAME);

        // 2. デフォルト値を定義
        $default = [
            'ai_slug_enabled' => '0', // デフォルトは無効
            'api_key' => '',
        ];

        // 3. 保存された値が配列でない場合は、デフォルト値を返す
        if (! is_array($saved)) {
            return $default;
        }

        // 4. 保存されていないキーをデフォルト値で補完し、マージして返す
        return array_merge($default, $saved);
    }

    /**
     * 特定の機能が有効かどうかをチェックする。
     *
     * @param string $key オプションキー (例: 'ai_slug_enabled')
     * @return bool
     */
    public static function is_enabled($key)
    {
        $options = self::get_options();
        // チェックボックスの値は '1' または '0' で保存されるため、'1' と厳密に比較
        return isset($options[$key]) && $options[$key] === '1';
    }

    /**
     * AIスラッグ生成機能が有効かどうかを返す。
     *
     * @return bool
     */
    public static function is_ai_slug_enabled()
    {
        return self::is_enabled('ai_slug_enabled');
    }

    /**
     * 保存されている Gemini API キーを返す。
     *
     * @return string
     */
    public static function get_api_key()
    {
        $options = self::get_options();
        // APIキーは文字列として取得し、安全のためにトリミング
        return isset($options['api_key']) ? trim($options['api_key']) : '';
    }
}
