<?php
class AuroraDesignBlocks_Popular_Posts_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'popular_posts_widget',
            __('[aurora-design-blocks]Popular Posts Widget', 'aurora-design-blocks'),
            ['description' => __('A widget that displays popular posts based on view count', 'aurora-design-blocks')]
        );
    }




    /**
     * 指定期間の日数分のアクセス数集計で人気記事を取得
     *
     * @param int $days 過去何日間を集計するか
     * @param int $limit 取得件数（トップN）
     * @return array 投稿IDとアクセス数の連想配列リスト
     */


    private static function format_popular_post_results($results)
    {
        $popular_posts = [];

        if ($results) {
            foreach ($results as $row) {
                $popular_posts[] = [
                    'post_id' => (int) $row->post_id,
                    'views'   => (int) $row->total_views,
                ];
            }
        }

        return $popular_posts;
    }

    public static function get_popular_posts_by_days($days = 30, $limit = 5)
    {
        global $wpdb;


        $cache_key = "adb_popular_posts_{$days}_{$limit}";
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return self::format_popular_post_results($cached);
        }


        $table_name = $wpdb->prefix . 'auroradesignblocks_access_ct';

        $date_limit = date('Y-m-d', strtotime("-{$days} days", current_time('timestamp')));

        // SQLで期間内のpost_idごとにview_count合計を取得し多い順でLIMIT付き
        $sql = $wpdb->prepare(
            "SELECT post_id, SUM(view_count) AS total_views
             FROM " . $table_name . "
             WHERE view_date >= %s
             GROUP BY post_id
             ORDER BY total_views DESC
             LIMIT %d",
            $date_limit,
            $limit
        );

        $results = $wpdb->get_results($sql);
        set_transient($cache_key, $results, 5 * MINUTE_IN_SECONDS); // 5分キャッシュ

        return self::format_popular_post_results($results);
    }












    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        // 設定値を取得
        $limit = !empty($instance['number']) ? absint($instance['number']) : 5;
        $days = !empty($instance['days']) ? absint($instance['days']) : 30;
        $show_views = isset($instance['show_views']) ? (bool) $instance['show_views'] : true;
        $show_thumbnail = isset($instance['show_thumbnail']) ? (bool) $instance['show_thumbnail'] : true;


        // 人気記事取得
        $popular_posts = AuroraDesignBlocks_Popular_Posts_Widget::get_popular_posts_by_days($days, $limit);

        if (!empty($popular_posts)) {
            echo '<ul>';
            foreach ($popular_posts as $item) {
                $post = get_post($item['post_id']);
                if ($post) {
                    $title = esc_html(get_the_title($post));
                    $permalink = esc_url(get_permalink($post));
                    $thumb_url = AuroraDesignBlocksPostThumbnail::getUrl($post->ID, 'thumbnail');

                    echo "<li>";
                    echo "<a href='{$permalink}'>";

                    if ($show_thumbnail && $thumb_url) {
                        echo "<img src='{$thumb_url}' alt='{$title}'>";
                    }

                    echo "{$title}";
                    if ($show_views) {
                        echo " ({$item['views']})";
                    }
                    echo "</a></li>";
                }
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('No popular posts yet.', 'aurora-design-blocks') . '</p>';
        }

        echo $args['after_widget'];
    }




    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('Popular Posts', 'aurora-design-blocks');
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $days = !empty($instance['days']) ? absint($instance['days']) : 30;
        $show_views = isset($instance['show_views']) ? (bool) $instance['show_views'] : true;
        $show_thumbnail = isset($instance['show_thumbnail']) ? (bool) $instance['show_thumbnail'] : true;

?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'aurora-design-blocks'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php _e('Number of posts to display:', 'aurora-design-blocks'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>"
                name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1"
                value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('days')); ?>"><?php _e('Aggregation period (days):', 'aurora-design-blocks'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('days')); ?>"
                name="<?php echo esc_attr($this->get_field_name('days')); ?>" type="number" step="1" min="1"
                value="<?php echo esc_attr($days); ?>" size="3">
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_views); ?> id="<?php echo esc_attr($this->get_field_id('show_views')); ?>" name="<?php echo esc_attr($this->get_field_name('show_views')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_views')); ?>"><?php _e('Display view count', 'aurora-design-blocks'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_thumbnail); ?> id="<?php echo esc_attr($this->get_field_id('show_thumbnail')); ?>" name="<?php echo esc_attr($this->get_field_name('show_thumbnail')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_thumbnail')); ?>"><?php _e('Display thumbnails', 'aurora-design-blocks'); ?></label>
        </p>

<?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['number'] = absint($new_instance['number']);
        $instance['days'] = absint($new_instance['days']);
        $instance['show_views'] = !empty($new_instance['show_views']) ? 1 : 0;
        $instance['show_thumbnail'] = !empty($new_instance['show_thumbnail']) ? 1 : 0;


        return $instance;
    }
}

// ウィジェット登録
function AuroraDesignBlocks_register_popular_posts_widget()
{
    register_widget('AuroraDesignBlocks_Popular_Posts_Widget');
}
add_action('widgets_init', 'AuroraDesignBlocks_register_popular_posts_widget');























/**********************************************************     */
/* データベース処理s  */
/**********************************************************     */

class AuroraDesignBlocks_PostViewTracker
{

    private static $table_name;

    public static function init()
    {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'auroradesignblocks_access_ct';

        // 投稿表示時のカウント処理
        add_action('wp', [__CLASS__, 'maybe_record_view']);
    }

    public static function create_views_table()
    {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'auroradesignblocks_access_ct';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            view_date DATE NOT NULL,
            view_count INT(11) UNSIGNED NOT NULL DEFAULT 1,
            PRIMARY KEY  (id),
            UNIQUE KEY post_date_unique (post_id, view_date),
            KEY post_id_idx (post_id),
            KEY view_date_idx (view_date)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function maybe_record_view()
    {
        if (is_user_logged_in() || is_admin() || wp_doing_ajax()) return;

        if (is_single()) {
            global $post;
            self::record_post_view($post->ID);
        }
    }

    public static function record_post_view($post_id)
    {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'auroradesignblocks_access_ct';
        $today = current_time('Y-m-d');

        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM " . self::$table_name . " WHERE post_id = %d AND view_date = %s",
                $post_id,
                $today
            )
        );

        if ($existing) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE " . self::$table_name . " SET view_count = view_count + 1 WHERE id = %d",
                    $existing
                )
            );
        } else {
            $wpdb->insert(
                self::$table_name,
                [
                    'post_id'    => $post_id,
                    'view_date'  => $today,
                    'view_count' => 1
                ],
                ['%d', '%s', '%d']
            );
        }
    }
}

AuroraDesignBlocks_PostViewTracker::init();


/**********************************************************     */
/* データベース処理e  */
/**********************************************************     */
