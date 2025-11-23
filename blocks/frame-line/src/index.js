/* frame-line*/
import { __experimentalNumberControl as NumberControl } from '@wordpress/components';
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
    ToggleControl,
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
        frameLineAlign: { type: 'string', default: 'left' },
        backgroundColor: { type: 'string' },
        titleColor: { type: 'string', default: 'block' },
        borderColor: { type: 'string', default: 'lightgreen' },
        borderStyle: { type: 'string', default: 'solid' },
        borderWidth: { type: 'string', default: '1px' },
        borderRadius: { type: 'string', default: '10px' },
        titleBorderRadius: { type: 'string', default: '0px' },
        showTitle: { type: 'boolean', default: true },

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
            borderRadius,
            titleBorderRadius,
            showTitle
        } = attributes;

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
                    <PanelBody title={__('Title Settings', 'aurora-design-blocks')}>
                        <ToggleControl
                            label={__('Show Title', 'aurora-design-blocks')}
                            checked={!!attributes.showTitle}
                            onChange={(val) => setAttributes({ showTitle: val })}
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
                            label={__('Title Border Radius', 'aurora-design-blocks')}
                            value={titleBorderRadius}
                            options={[
                                { label: __('Normal (0px)', 'aurora-design-blocks'), value: '0px' },
                                { label: __('Rounded (8px)', 'aurora-design-blocks'), value: '8px' },
                                { label: __('More Rounded (16px)', 'aurora-design-blocks'), value: '16px' },
                            ]}
                            onChange={(val) => setAttributes({ titleBorderRadius: val })}
                        />

                        <PanelColorSettings
                            title={__('Title Color', 'aurora-design-blocks')}
                            colorSettings={[
                                {
                                    label: __('Title Text Color', 'aurora-design-blocks'),
                                    value: titleColor,
                                    onChange: (value) => setAttributes({ titleColor: value }),
                                },
                            ]}
                        />
                    </PanelBody>
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

                            ]}
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
                        <NumberControl
                            label={__('Border Width (px)', 'aurora-design-blocks')}
                            value={parseInt(borderWidth)}
                            onChange={(val) => setAttributes({ borderWidth: `${val}px` })}
                            min={0}
                        />

                        <NumberControl
                            label={__('Border Radius (px)', 'aurora-design-blocks')}
                            value={parseInt(borderRadius)}
                            onChange={(val) => setAttributes({ borderRadius: `${val}px` })}
                            min={0}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    {showTitle && (
                        <RichText
                            tagName="div"
                            className={`frame-line-title frame-line-title-${frameLineAlign}`}
                            placeholder={__('Enter title...', 'aurora-design-blocks')}
                            value={title}
                            onChange={(val) => setAttributes({ title: val })}
                            style={{
                                backgroundColor: borderColor || 'white',
                                color: titleColor,
                                borderRadius: titleBorderRadius,

                            }}
                        />)}
                    <div className="frame-line-content">
                        <InnerBlocks />
                    </div>
                </div>
            </>
        );
    },

    save: ({ attributes }) => {
        const { title, frameLineAlign, borderStyle, backgroundColor, borderColor, titleColor, borderWidth, borderRadius, titleBorderRadius, showTitle } = attributes;

        const blockProps = useBlockProps.save({
            className: `frame-line border-${borderStyle} frame-line-${frameLineAlign}`,
            style: {
                backgroundColor,
                borderColor,
                borderStyle,
                borderWidth,
                borderRadius,
                showTitle,
            }
        });

        return (
            <div {...blockProps}>
                {showTitle && <RichText.Content
                    tagName="div"
                    className={`frame-line-title frame-line-title-${frameLineAlign}`}
                    value={title}
                    style={{
                        backgroundColor: borderColor || 'white',
                        color: titleColor,
                        borderRadius: titleBorderRadius,

                    }}
                />}
                <div className="frame-line-content">
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    },
});
