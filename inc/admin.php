<?php
if (! defined('ABSPATH')) exit;

class AuroraDesignBlocks_AdminTop
{

    private static $instance = null;
    public $tabs; // ★ public に変更: 外部のタブクラスからアクセスできるようにする

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // タブマネージャーを初期化
        $this->tabs = new AuroraDesignBlocks_AdminTabs();

        // ★★★ 変更なし/静的登録呼び出しを削除済み ★★★
        // 管理画面メニューを追加
        add_action('admin_menu', [$this, 'add_menu']);
    }

    public function add_menu()
    {
        add_menu_page(
            __('Aurora Design Blocks', 'aurora-design-blocks'), // ページタイトル
            __('Aurora Design Blocks', 'aurora-design-blocks'), // メニュータイトル
            'manage_options',                                    // 権限
            'aurora-design-blocks',                              // スラッグ
            [$this, 'render'],                                   // コールバック
            'dashicons-layout',                                  // アイコン
            60                                                   // 表示順
        );
    }

    /**
     * メインページをレンダリング
     */
    public function render()
    {
        $tab = isset($_GET['tab'])
            ? sanitize_text_field(wp_unslash($_GET['tab']))
            : $this->tabs->get_default();
?>
        <div class="wrap">
            <h1><?php echo esc_html__('Aurora Design Blocks', 'aurora-design-blocks'); ?></h1>

            <nav class="nav-tab-wrapper">
                <?php $this->tabs->render_tabs($tab); ?>
            </nav>

            <div class="tab-content">
                <?php $this->tabs->render_page($tab); ?>
            </div>
        </div>
<?php
    }
}


if (! defined('ABSPATH')) exit;

class AuroraDesignBlocks_AdminTabs
{

    private $tabs = [];

    public function __construct()
    {
        // ★★★ 変更なし/コンストラクタで直接インスタンス化する処理を削除済み ★★★
    }

    /**
     * [新規メソッド] 外部からタブを登録するためのパブリックメソッド
     * @param string $slug タブのスラッグ
     * @param string $class_name タブページクラス名
     */
    public function add_tab($slug, $class_name)
    {
        // クラス名を受け取り、内部でインスタンス化する
        $this->tabs[$slug] = new $class_name();
    }

    /**
     * デフォルトタブを返す
     */
    public function get_default()
    {
        return 'about';
    }

    /**
     * タブリンクを描画
     */
    public function render_tabs($current)
    {
        foreach ($this->tabs as $key => $obj) {
            $active = $current === $key ? 'nav-tab-active' : '';
            echo '<a href="?page=aurora-design-blocks&tab=' . esc_attr($key) . '" class="nav-tab ' . esc_attr($active)   . '">'
                . esc_html($obj->get_label()) .
                '</a>';
        }
    }

    /**
     * タブページを描画
     */
    public function render_page($current)
    {
        if (isset($this->tabs[$current])) {
            $this->tabs[$current]->render_page();
        } else {
            echo '<p>' . esc_html__('Tab not found.', 'aurora-design-blocks') . '</p>';
        }
    }
}



// メインの実行開始点（プラグインファイルの末尾など）
// 最初にメイン管理クラスを初期化する
AuroraDesignBlocks_AdminTop::get_instance();

// ★★★ 変更箇所：各タブクラスの静的登録メソッドを呼び出す ★★★
// タブを追加したい場合は、ここにそのクラスの register_hooks() を1行追加するだけ