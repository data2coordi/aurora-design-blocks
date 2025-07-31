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
        // Check if the function exists before calling it (good practice)

        $content = file_get_contents(AURORA_DESIGN_BLOCKS_PATH . 'patterns/frame-line1.html');

        register_block_pattern(
            'AuroraDesignBlocks/frame-line-pattern1',
            array(
                'title'       => __('AuroraDesignBlocks frame-line1', 'AuroraDesignBlocks'),
                'categories'  => array('aurora-design-blocks'),
                'content'     => $content,
            )
        );

        $content = file_get_contents(AURORA_DESIGN_BLOCKS_PATH . 'patterns/frame-line2.html');
        register_block_pattern(
            'AuroraDesignBlocks/frame-line-pattern2',
            array(
                'title'       => __('AuroraDesignBlocks frame-line1', 'AuroraDesignBlocks'),
                'categories'  => array('aurora-design-blocks'),
                'content'     => $content,
            )
        );
    }
}

// Instantiate the class to initialize the functionality
new AuroraDesignBlocks_Block_Assets();




/********************************************************************/
/* ブロックエディター用のパターン登録e*/
/********************************************************************/
