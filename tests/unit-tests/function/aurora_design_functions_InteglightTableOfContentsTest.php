<?php // tests/unit-tests/AuroraDesignBlocksTableOfContentsTest.php

declare(strict_types=1);

/**
 * Test case for the AuroraDesignBlocksTableOfContents class.
 *
 * @coversDefaultClass AuroraDesignBlocksTableOfContents
 * @group functions
 * @group toc
 */
class aurora_design_functions_AuroraDesignBlocksTableOfContentsTest extends WP_UnitTestCase
{
    /**
     * Test target class instance.
     * @var AuroraDesignBlocksTableOfContents|null
     */
    private $instance = null;

    /**
     * Post ID created for testing.
     * @var int|null
     */
    private $test_post_id = null;

    /**
     * Original $_POST array.
     * @var array
     */
    private $original_post_global;

    /**
     * Set up the test environment before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        // Instantiate the class (this also adds the hooks)
        $this->instance = new AuroraDesignBlocksTableOfContents();
        // Create a post for testing content filter and meta boxes
        $this->test_post_id = self::factory()->post->create();
        // Backup $_POST
        $this->original_post_global = $_POST;
    }

    /**
     * Clean up the test environment after each test.
     */
    public function tearDown(): void
    {
        // Remove hooks added by the constructor
        remove_filter('the_content', [$this->instance, 'add_toc_to_content']);
        remove_action('add_meta_boxes', [$this->instance, 'add_toc_visibility_meta_box']);
        remove_action('save_post', [$this->instance, 'save_toc_visibility_meta_box_data']);

        // Clean up created post and meta
        if ($this->test_post_id) {
            wp_delete_post($this->test_post_id, true);
            delete_post_meta($this->test_post_id, 'hide_toc');
        }
        unset($this->instance);
        unset($this->test_post_id);
        // Restore $_POST
        $_POST = $this->original_post_global;

        parent::tearDown();
    }

    /**
     * @test
     * @covers ::__construct
     * Test if constructor adds necessary hooks.
     */
    public function constructor_should_add_hooks(): void
    {
        $this->assertGreaterThan(0, has_filter('the_content', [$this->instance, 'add_toc_to_content']));
        $this->assertGreaterThan(0, has_action('add_meta_boxes', [$this->instance, 'add_toc_visibility_meta_box']));
        $this->assertGreaterThan(0, has_action('save_post', [$this->instance, 'save_toc_visibility_meta_box_data']));
    }

    /**
     * @test
     * @covers ::add_toc_to_content
     * Test if TOC is added correctly when headings exist.
     */
    public function add_toc_to_content_should_add_toc_and_ids_when_headings_exist(): void
    {
        // Arrange
        $content = <<<HTML
<h2>First Heading</h2>
<p>Some content.</p>
<h3>Sub Heading</h3>
<p>More content.</p>
<h1>Another Top Heading</h1>
HTML;
        // Simulate being on the test post page
        $this->go_to(get_permalink($this->test_post_id));
        update_post_meta($this->test_post_id, 'hide_toc', '0'); // Ensure TOC is not hidden

        // Act
        $filtered_content = $this->instance->add_toc_to_content($content);

        // Assert
        // Check if TOC div exists
        $this->assertStringContainsString('<div class="post-toc">', $filtered_content);
        $this->assertStringContainsString('<B>Index</B>', $filtered_content);
        $this->assertStringContainsString('<ul>', $filtered_content);
        $this->assertStringContainsString('</ul></div>', $filtered_content);

        // Check TOC list items with links and correct indentation
        $this->assertStringContainsString('<li class="toc-h2">&nbsp;&nbsp;<a href="#first-heading">First Heading</a></li>', $filtered_content);
        $this->assertStringContainsString('<li class="toc-h3">&nbsp;&nbsp;&nbsp;&nbsp;<a href="#sub-heading">Sub Heading</a></li>', $filtered_content);
        $this->assertStringContainsString('<li class="toc-h1"><a href="#another-top-heading">Another Top Heading</a></li>', $filtered_content); // H1 has no indent

        // Check if IDs are added to original headings
        $this->assertStringContainsString('<h2 id="first-heading">First Heading</h2>', $filtered_content);
        $this->assertStringContainsString('<h3 id="sub-heading">Sub Heading</h3>', $filtered_content);
        $this->assertStringContainsString('<h1 id="another-top-heading">Another Top Heading</h1>', $filtered_content);

        // Check if TOC is prepended
        $this->assertStringStartsWith('<div class="post-toc">', $filtered_content);
    }

    /**
     * @test
     * @covers ::add_toc_to_content
     * Test if content remains unchanged when no headings exist.
     */
    public function add_toc_to_content_should_return_original_content_when_no_headings(): void
    {
        // Arrange
        $content = "<p>This content has no headings.</p>";
        $this->go_to(get_permalink($this->test_post_id));
        update_post_meta($this->test_post_id, 'hide_toc', '0');

        // Act
        $filtered_content = $this->instance->add_toc_to_content($content);

        // Assert
        $this->assertEquals($content, $filtered_content);
        $this->assertStringNotContainsString('<div class="post-toc">', $filtered_content);
    }

    /**
     * @test
     * @covers ::add_toc_to_content
     * Test if content remains unchanged when TOC is hidden via meta.
     */
    public function add_toc_to_content_should_return_original_content_when_hidden_by_meta(): void
    {
        // Arrange
        $content = "<h2>A Heading</h2><p>Some content.</p>";
        $this->go_to(get_permalink($this->test_post_id));
        update_post_meta($this->test_post_id, 'hide_toc', '1'); // Hide TOC

        // Act
        $filtered_content = $this->instance->add_toc_to_content($content);

        // Assert
        $this->assertEquals($content, $filtered_content);
        $this->assertStringNotContainsString('<div class="post-toc">', $filtered_content);
        $this->assertStringNotContainsString('id="a-heading"', $filtered_content); // ID should not be added either
    }

    /**
     * @test
     * @covers ::add_toc_visibility_meta_box
     * Test if the meta box adding function is called.
     * Note: Directly testing add_meta_box registration is complex in WP_UnitTestCase.
     * We'll test if the action triggers the method. A more robust test might involve mocking.
     */
    public function add_toc_visibility_meta_box_should_be_callable(): void
    {
        // This test mainly ensures the method exists and is callable via the hook.
        // We rely on the constructor test for hook registration confirmation.
        $this->assertTrue(method_exists($this->instance, 'add_toc_visibility_meta_box'));
        // We can manually call the action to ensure no fatal errors occur,
        // though it doesn't fully verify add_meta_box parameters.
        do_action('add_meta_boxes', 'post', get_post($this->test_post_id));
        // If the above line runs without error, it's a basic check.
        $this->assertTrue(true, 'Executing add_meta_boxes action did not cause fatal errors.');
    }


    /**
     * @test
     * @covers ::render_toc_visibility_meta_box
     * Test rendering of the meta box content when meta value is '0' (unchecked).
     */
    public function render_toc_visibility_meta_box_should_output_unchecked_checkbox(): void
    {
        // Arrange
        $post = get_post($this->test_post_id);
        update_post_meta($this->test_post_id, 'hide_toc', '0');

        // Act
        ob_start();
        $this->instance->render_toc_visibility_meta_box($post);
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('name="hide_toc"', $output);
        $this->assertStringContainsString('value="1"', $output);
        $this->assertStringContainsString('id="hide_toc"', $output);
        $this->assertStringContainsString(__('Hide TOC', 'integlight'), $output);
        $this->assertStringContainsString('wp_nonce_field(\'toc_visibility_nonce_action\', \'toc_visibility_nonce\')', $this->get_reflected_method_body('render_toc_visibility_meta_box')); // Check nonce call via reflection
        $this->assertStringNotContainsString('checked=\'checked\'', $output); // Should not be checked
    }

    /**
     * @test
     * @covers ::render_toc_visibility_meta_box
     * Test rendering of the meta box content when meta value is '1' (checked).
     */
    public function render_toc_visibility_meta_box_should_output_checked_checkbox(): void
    {
        // Arrange
        $post = get_post($this->test_post_id);
        update_post_meta($this->test_post_id, 'hide_toc', '1');

        // Act
        ob_start();
        $this->instance->render_toc_visibility_meta_box($post);
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('name="hide_toc"', $output);
        $this->assertStringContainsString('value="1"', $output);
        $this->assertStringContainsString('id="hide_toc"', $output);
        $this->assertStringContainsString(__('Hide TOC', 'integlight'), $output);
        $this->assertStringContainsString('wp_nonce_field(\'toc_visibility_nonce_action\', \'toc_visibility_nonce\')', $this->get_reflected_method_body('render_toc_visibility_meta_box'));
        $this->assertStringContainsString('checked=\'checked\'', $output); // Should be checked
    }

    /**
     * @test
     * @covers ::save_toc_visibility_meta_box_data
     * Test saving meta data when checkbox is checked.
     */
    public function save_toc_visibility_meta_box_data_should_save_one_when_checked(): void
    {
        // Arrange
        $user_id = self::factory()->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        $_POST['toc_visibility_nonce'] = wp_create_nonce('toc_visibility_nonce_action');
        $_POST['hide_toc'] = '1'; // Simulate checkbox being checked

        // Act
        $this->instance->save_toc_visibility_meta_box_data($this->test_post_id);

        // Assert
        $this->assertEquals('1', get_post_meta($this->test_post_id, 'hide_toc', true));
    }

    /**
     * @test
     * @covers ::save_toc_visibility_meta_box_data
     * Test saving meta data when checkbox is unchecked.
     */
    public function save_toc_visibility_meta_box_data_should_save_zero_when_unchecked(): void
    {
        // Arrange
        $user_id = self::factory()->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        $_POST['toc_visibility_nonce'] = wp_create_nonce('toc_visibility_nonce_action');
        // $_POST['hide_toc'] is not set, simulating unchecked checkbox

        // Set initial value to '1' to ensure it changes
        update_post_meta($this->test_post_id, 'hide_toc', '1');

        // Act
        $this->instance->save_toc_visibility_meta_box_data($this->test_post_id);

        // Assert
        $this->assertEquals('0', get_post_meta($this->test_post_id, 'hide_toc', true));
    }

    /**
     * @test
     * @covers ::save_toc_visibility_meta_box_data
     * Test that saving is skipped if nonce is invalid.
     */
    public function save_toc_visibility_meta_box_data_should_skip_if_nonce_invalid(): void
    {
        // Arrange
        $user_id = self::factory()->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        $_POST['toc_visibility_nonce'] = 'invalid-nonce';
        $_POST['hide_toc'] = '1';
        update_post_meta($this->test_post_id, 'hide_toc', '0'); // Initial value

        // Act
        $this->instance->save_toc_visibility_meta_box_data($this->test_post_id);

        // Assert
        $this->assertEquals('0', get_post_meta($this->test_post_id, 'hide_toc', true), 'Meta should not be updated with invalid nonce.');
    }

    /**
     * @test
     * @covers ::save_toc_visibility_meta_box_data
     * Test that saving is skipped if user lacks permission.
     */
    public function save_toc_visibility_meta_box_data_should_skip_if_no_permission(): void
    {
        // Arrange
        $user_id = self::factory()->user->create(['role' => 'subscriber']); // Insufficient role
        wp_set_current_user($user_id);
        $_POST['toc_visibility_nonce'] = wp_create_nonce('toc_visibility_nonce_action');
        $_POST['hide_toc'] = '1';
        update_post_meta($this->test_post_id, 'hide_toc', '0'); // Initial value

        // Act
        $this->instance->save_toc_visibility_meta_box_data($this->test_post_id);

        // Assert
        $this->assertEquals('0', get_post_meta($this->test_post_id, 'hide_toc', true), 'Meta should not be updated without permission.');
    }

    public function save_toc_visibility_meta_box_data_should_skip_during_autosave(): void
    {
        // Arrange
        $user_id = self::factory()->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);

        // 元の投稿ID (setUp で作成されたもの)
        $original_post_id = $this->test_post_id;
        update_post_meta($original_post_id, 'hide_toc', '0'); // 元の投稿に初期値を設定

        // --- オートセーブをシミュレート ---
        // 1. オートセーブ用のデータを作成 (編集中の動作を模倣)
        $autosave_data = [
            'post_ID'      => $original_post_id, // 親投稿のID
            'post_title'   => 'Autosave Revision Title',
            'post_content' => 'Autosave revision content.',
            // 必要であれば、オートセーブ中に存在する可能性のある他のフィールドを追加
        ];
        // 2. wp_create_post_autosave() を使用してオートセーブリビジョンを作成
        //    この関数は正しい post_name フォーマット (例: {parent_id}-autosave-v1) を設定します
        $autosave_revision_id = wp_create_post_autosave($autosave_data);

        // オートセーブリビジョンが正常に作成されたか確認 (失敗時はテストが失敗する)
        $this->assertIsInt($autosave_revision_id, 'Failed to create autosave revision.');
        $this->assertGreaterThan(0, $autosave_revision_id, 'Autosave revision ID should be positive.');
        // --- オートセーブのシミュレートここまで ---

        // $_POST データを設定 (nonce, チェックボックスの値)
        $_POST['toc_visibility_nonce'] = wp_create_nonce('toc_visibility_nonce_action');
        $_POST['hide_toc'] = '1'; // チェックボックスがチェックされた状態をシミュレート

        // Act
        // save_toc_visibility_meta_box_data メソッドを【オートセーブリビジョンのID】で呼び出す
        // これにより、メソッド内の wp_is_post_autosave($autosave_revision_id) が true を返すはず
        $this->instance->save_toc_visibility_meta_box_data($autosave_revision_id);

        // Assert
        // メタデータが【元の投稿ID】で更新されていないことを確認
        $this->assertEquals('0', get_post_meta($original_post_id, 'hide_toc', true), 'Meta should not be updated during autosave.');

        // このテスト固有の後処理 (ユーザー、$_POST)
        wp_set_current_user(0);
        unset($_POST['toc_visibility_nonce']);
        unset($_POST['hide_toc']);
        // 作成されたオートセーブリビジョンは、WP_UnitTestCase の後処理で自動的に削除されます
    }



    /**
     * Helper function to get method body using reflection (for checking function calls).
     * @param string $methodName
     * @return string
     * @throws ReflectionException
     */
    private function get_reflected_method_body(string $methodName): string
    {
        $reflector = new ReflectionClass($this->instance);
        $method = $reflector->getMethod($methodName);
        $filename = $method->getFileName();
        $start_line = $method->getStartLine();
        $end_line = $method->getEndLine();
        $length = $end_line - $start_line;

        $source = file($filename);
        return implode("", array_slice($source, $start_line, $length));
    }
}
