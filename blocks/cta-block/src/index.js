import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

import './style.css';

registerBlockType('aurora-design-blocks/cta-block', {
    attributes: {
        isFixed: { type: 'boolean', default: false }
    },

    edit: ({ attributes, setAttributes }) => {
        const { isFixed } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title="CTA配置設定">
                        <ToggleControl
                            label="固定フロートにする"
                            checked={isFixed}
                            onChange={(value) => setAttributes({ isFixed: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className={`cta-block ${isFixed ? 'fixed' : ''}`}>
                    <InnerBlocks allowedBlocks={['core/heading', 'core/paragraph', 'core/button']} />
                </div>
            </>
        );
    },

    save: ({ attributes }) => {
        const { isFixed } = attributes;
        const blockClasses = ['cta-block'];
        if (isFixed) blockClasses.push('fixed');

        return (
            <div className={blockClasses.join(' ')}>
                <InnerBlocks.Content />
            </div>
        );
    }
});
