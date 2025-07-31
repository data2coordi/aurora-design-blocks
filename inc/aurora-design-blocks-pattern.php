<?php

/********************************************************************/
/* ブロックエディター用のパターン登録s*/
/********************************************************************/


/**
 * Class AuroraDesignBlocks_Block_Assets
 *
 * Registers custom block styles and patterns for the theme.
 */
class AuroraDesignBlocks_Block_Assets
{

    /**
     * Constructor. Hooks into WordPress init action.
     */
    public function __construct()
    {
        // Both styles and patterns should be registered during the 'init' action.
        add_action('init', [$this, 'register_assets']);
    }

    /**
     * Registers both block styles and patterns.
     * Action: init
     */
    public function register_assets()
    {
        register_block_pattern_category(
            'aurora-design-blocks',
            ['label' => __('Aurora Design Blocks', 'aurora-design-blocks')]
        );

        $this->register_block_patterns();
    }


    /**
     * Registers custom block patterns.
     * Called by register_assets during the 'init' action.
     */
    private function register_block_patterns()
    {

        $patterns = [
            'frame-line1' => [
                'title' => __('AuroraDesignBlocks pattern frame-line1', 'AuroraDesignBlocks'),
                'file' => 'frame-line1.html',
            ],
            'frame-line2' => [
                'title' => __('AuroraDesignBlocks pattern frame-line2', 'AuroraDesignBlocks'),
                'file' => 'frame-line2.html',
            ],
            'slider1' => [
                'title' => __('AuroraDesignBlocks pattern slider1', 'AuroraDesignBlocks'),
                'file' => 'slider1.html',
            ],
        ];

        foreach ($patterns as $slug => $data) {
            $content = file_get_contents(AURORA_DESIGN_BLOCKS_PATH . 'patterns/' . $data['file']);
            register_block_pattern("AuroraDesignBlocks/$slug", [
                'title' => $data['title'],
                'categories' => ['aurora-design-blocks'],
                'content' => $content,
            ]);
        }
    }
}

// Instantiate the class to initialize the functionality
new AuroraDesignBlocks_Block_Assets();




/********************************************************************/
/* ブロックエディター用のパターン登録e*/
/********************************************************************/
