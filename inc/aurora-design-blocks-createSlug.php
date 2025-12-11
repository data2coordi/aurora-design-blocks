

<?php

/********************************************************************/
/* google geminiでスラッグ対応 s */
/********************************************************************/

require_once plugin_dir_path(ADB_PLUGIN_FILE) . 'vendor/autoload.php';

use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;

class Aurora_GeminiAI_Slug_Generator
{
    private static $instance = null;

    // 保存前の旧投稿データ保持用
    private static $old_posts = [];

    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // 保存前に旧データを取得
        add_filter('wp_insert_post_data', array($this, 'capture_old_post_data'), 10, 2);

        // 保存後のメイン処理
        add_action('wp_after_insert_post', array($this, 'handle_post_after_insert'), 20, 3);
    }

    /**
     * ① 保存前の旧データをキャプチャ
     */
    public function capture_old_post_data($data, $postarr)
    {
        if (!empty($postarr['ID'])) {
            $old = get_post($postarr['ID']);
            if ($old) {
                self::$old_posts[$postarr['ID']] = clone $old; // 保存前のスナップショット
            }
        }
        return $data;
    }

    /**
     * ② 保存後の処理（slug の初回生成を検知）
     */
    public function handle_post_after_insert($post_id, $post, $update)
    {
        error_log('=== wp_after_insert_post DEBUG START =============================');
        error_log('post_id: ' . $post_id);
        error_log('post_title: ' . $post->post_title);
        error_log('post_name: ' . $post->post_name);
        error_log('post_status: ' . $post->post_status);
        error_log('update: ' . ($update ? 'true' : 'false'));
        error_log('============================================================');

        // リビジョン除外
        if (wp_is_post_revision($post_id)) {
            error_log('!!!skip: revision');
            return;
        }

        // タイトル空は対象外
        if (empty($post->post_title)) {
            error_log('!!!skip: title empty');
            return;
        }

        // auto-draft は slug 空なので対象外
        if ($post->post_status === 'auto-draft') {
            error_log('!!!skip: auto-draft');
            return;
        }

        // 保存前の旧データを取得
        $old = self::$old_posts[$post_id] ?? null;

        if (!$old) {
            error_log('!!!skip: old post not found (first insert?)');
            return;
        }

        $old_slug = $old->post_name;
        $new_slug = $post->post_name;

        error_log('old_slug: ' . $old_slug);
        error_log('new_slug: ' . $new_slug);

        // ★追加：すでに英語っぽい場合は処理しない
        if (preg_match('/^[a-z0-9\-]+$/', $new_slug)) {
            error_log('!!!skip: slug is already in English');
            return;
        }


        /**
         * ★コアが slug を初めて生成した瞬間を厳密に検知
         * 旧 slug = 空
         * 新 slug = 非空
         */
        $is_core_slug_generated =
            (empty($old_slug)) &&
            (!empty($new_slug));

        if (!$is_core_slug_generated) {
            error_log('!!!skip: コア初回スラッグ生成のタイミングでない');
            return;
        }

        error_log('@@@@@@@@@@@@@@@@@@@@@@@@@@@@ OK: コアの初回スラッグ生成を検出 → AI処理へ');


        /**************************************************************
         * ここから AI スラッグ処理
         **************************************************************/
        if (
            !class_exists('AuroraDesignBlocks_AdminFront_CreateSlug') ||
            !AuroraDesignBlocks_AdminFront_CreateSlug::is_ai_slug_enabled()
        ) {
            error_log('skip: AI disabled');
            return;
        }

        $gemini_api_key = AuroraDesignBlocks_AdminFront_CreateSlug::get_api_key();
        if (empty($gemini_api_key)) {
            error_log('skip: API key empty');
            return;
        }

        try {
            $client = new Client($gemini_api_key);

            $prompt = "Please translate the following title into a short phrase suitable for an English WordPress slug, replace half-width spaces with hyphens, and return the result in lowercase. If the title is already an English slug, return it without modification.\n\n"
                . "title: " . $post->post_title;

            $response = $client->generativeModel('gemini-2.5-flash')
                ->generateContent(new TextPart($prompt));

            $translated = $response->text();
            $ai_slug   = sanitize_title($translated);
            // 成功した場合、既存のエラーログをクリア
            delete_option('adb_gemini_last_error');
            error_log('AI result slug: ' . $ai_slug);

            wp_update_post([
                'ID'        => $post_id,
                'post_name' => $ai_slug,
            ]);

            error_log('AI slug updated.');
        } catch (\Exception $e) {
            error_log('Gemini API Error: ' . $e->getMessage());
            // ★★★ 失敗した場合、エラーメッセージをDBに保存 ★★★
            $error_message = 'Gemini API Error: ' . $e->getMessage();
            error_log($error_message);
            update_option('adb_gemini_last_error', $error_message);
        }

        error_log('=== wp_after_insert_post DEBUG END ===');
    }
}

Aurora_GeminiAI_Slug_Generator::get_instance();

/********************************************************************/
/* google geminiでスラッグ対応 e */
/********************************************************************/
