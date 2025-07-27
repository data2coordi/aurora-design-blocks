/* frame-line*/
import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InspectorControls,
    InnerBlocks,
    RichText,
    PanelColorSettings
} from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './style.css';
import './editor.css';

registerBlockType('aurora-design-blocks/frame-line', {
    apiVersion: 2,
    title: __('[aurora-design-blocks]frame-line', 'aurora-design-blocks'),
    category: 'design',
    icon: 'editor-table',
    supports: {
        align: ['wide', 'full'],
    },

    attributes: {
        title: { type: 'string', default: '' },
        frameLineAlign: { type: 'string', default: 'center' },
        backgroundColor: { type: 'string' },
        titleColor: { type: 'string' },
        borderColor: { type: 'string' },
        borderStyle: { type: 'string', default: 'solid' },
        borderWidth: { type: 'string', default: '1px' },
        borderRadius: { type: 'string', default: '0px' },
    },

    edit: ({ attributes, setAttributes }) => {
        const {
            title,
            frameLineAlign,
            backgroundColor,
            titleColor,
            borderColor,
            borderStyle,
            borderWidth,
            borderRadius } = attributes;

        const blockProps = useBlockProps({
            className: `frame-line border-${borderStyle} frame-line-${frameLineAlign}`,
            style: {
                backgroundColor,
                borderColor,
                borderStyle,
                borderWidth,
                borderRadius,
            }
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Frame-line Settings', 'aurora-design-blocks')}>
                        <PanelColorSettings
                            title={__('Frame-line Color', 'aurora-design-blocks')}
                            colorSettings={[
                                {
                                    label: __('Background Color', 'aurora-design-blocks'),
                                    value: backgroundColor,
                                    onChange: (value) => setAttributes({ backgroundColor: value }),
                                },
                                {
                                    label: __('Border Color', 'aurora-design-blocks'),
                                    value: borderColor,
                                    onChange: (value) => setAttributes({ borderColor: value }),
                                },
                                {
                                    label: __('Title Text Color', 'aurora-design-blocks'),
                                    value: titleColor,
                                    onChange: (value) => setAttributes({ titleColor: value }),
                                },
                            ]}
                        />
                        <SelectControl
                            label={__('Frame-line-title Alignment', 'aurora-design-blocks')}
                            value={frameLineAlign}
                            options={[
                                { label: __('Left', 'aurora-design-blocks'), value: 'left' },
                                { label: __('Center', 'aurora-design-blocks'), value: 'center' },
                                { label: __('Right', 'aurora-design-blocks'), value: 'right' },
                            ]}
                            onChange={(val) => setAttributes({ frameLineAlign: val })}
                        />
                        <SelectControl
                            label={__('Border Style', 'aurora-design-blocks')}
                            value={borderStyle}
                            options={[
                                { label: __('Solid', 'aurora-design-blocks'), value: 'solid' },
                                { label: __('Dashed', 'aurora-design-blocks'), value: 'dashed' },
                                { label: __('None', 'aurora-design-blocks'), value: 'none' },
                            ]}
                            onChange={(val) => setAttributes({ borderStyle: val })}
                        />
                        <TextControl
                            label={__('Border Width (e.g., 1px)', 'aurora-design-blocks')}
                            value={borderWidth}
                            onChange={(val) => setAttributes({ borderWidth: val })}
                        />
                        <TextControl
                            label={__('Border Radius (e.g., 4px)', 'aurora-design-blocks')}
                            value={borderRadius}
                            onChange={(val) => setAttributes({ borderRadius: val })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <RichText
                        tagName="div"
                        className={`frame-line-title frame-line-title-${frameLineAlign}`}
                        placeholder={__('Enter title...', 'aurora-design-blocks')}
                        value={title}
                        onChange={(val) => setAttributes({ title: val })}
                        style={{
                            backgroundColor: borderColor || 'white',
                            color: titleColor,
                        }}
                    />
                    <div className="frame-line-content">
                        <InnerBlocks />
                    </div>
                </div>
            </>
        );
    },

    save: ({ attributes }) => {
        const { title, frameLineAlign, borderStyle, backgroundColor, borderColor, titleColor, borderWidth, borderRadius } = attributes;

        const blockProps = useBlockProps.save({
            className: `frame-line border-${borderStyle} frame-line-${frameLineAlign}`,
            style: {
                backgroundColor,
                borderColor,
                borderStyle,
                borderWidth,
                borderRadius,
            }
        });

        return (
            <div {...blockProps}>
                {title && <RichText.Content
                    tagName="div"
                    className={`frame-line-title frame-line-title-${frameLineAlign}`}
                    value={title}
                    style={{
                        backgroundColor: borderColor || 'white',
                        color: titleColor,
                    }}
                />}
                <div className="frame-line-content">
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    },
});
