import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl } from '@wordpress/components';
import './style.css';

registerBlockType('aurora-design-blocks/cta-block', {
    attributes: {
        isFixed: { type: 'boolean', default: false },
        position: { type: 'string', default: 'top-right' },
    },

    edit: ({ attributes, setAttributes }) => {
        const { isFixed, position } = attributes;
        const blockProps = useBlockProps({
            className: `cta-block ${isFixed ? 'fixed' : ''} position-${position}`
        });

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
                <div {...blockProps}>
                    <div className="cta-inner">
                        <InnerBlocks
                            allowedBlocks={['core/heading', 'core/paragraph', 'core/button', 'core/columns']}
                            renderAppender={InnerBlocks.ButtonBlockAppender}
                        />
                    </div>
                </div>
            </>
        );
    },

    save: ({ attributes }) => {
        const { isFixed, position } = attributes;
        const blockProps = useBlockProps.save({
            className: `cta-block ${isFixed ? 'fixed' : ''} position-${position}`
        });

        return (
            <div {...blockProps}>
                <div className="cta-inner">
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
