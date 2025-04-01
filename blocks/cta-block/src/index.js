import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl } from '@wordpress/components';
import './style.css';

registerBlockType('aurora-design-blocks/cta-block', {
    attributes: {
        isFixed: { type: 'boolean', default: false },
        position: { type: 'string', default: 'top-right' }, // 初期位置を 'top-right' に設定
    },

    edit: ({ attributes, setAttributes }) => {
        const { isFixed, position } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title="CTA配置設定">
                        <ToggleControl
                            label="固定フロートにする"
                            checked={isFixed}
                            onChange={(value) => setAttributes({ isFixed: value })}
                        />
                        <SelectControl
                            label="配置位置"
                            value={position}
                            options={[
                                { label: '右上', value: 'top-right' },
                                { label: '左上', value: 'top-left' },
                                { label: '右下', value: 'bottom-right' },
                                { label: '左下', value: 'bottom-left' },
                            ]}
                            onChange={(value) => setAttributes({ position: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className={`cta-block ${isFixed ? 'fixed' : ''} position-${position}`}>
                    <InnerBlocks allowedBlocks={['core/heading', 'core/paragraph', 'core/button']} />
                </div>
            </>
        );
    },

    save: ({ attributes }) => {
        const { isFixed, position } = attributes;
        const blockClasses = ['cta-block'];
        if (isFixed) blockClasses.push('fixed');
        blockClasses.push(`position-${position}`);

        return (
            <div className={blockClasses.join(' ')}>
                <InnerBlocks.Content />
            </div>
        );
    }
});
