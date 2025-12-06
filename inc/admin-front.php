<?php
class AuroraDesignBlocks_Enable_Flags
{

    private static function get_options()
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

    private static function is_enabled($key)
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
