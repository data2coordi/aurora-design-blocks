import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ColorPicker, SelectControl } from '@wordpress/components';
import './style.css';
import './editor.css';

import { __ } from '@wordpress/i18n';

const calculateFontSize = (fontSize) => {
    //コンテンツ幅800を基準にする。
    const baseSize = (fontSize / 800) * 100;
    return `${baseSize}vw`;
};

registerBlockType('aurora-design-blocks/text-flow-animation', {
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps({ className: "aurora-design-blocks-text-flow-editor" });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__("Text setting", "aurora-design-blocks")}>
                        <RangeControl
                            label={__("Font size", "aurora-design-blocks")}
                            value={attributes.fontSize}
                            onChange={(value) => setAttributes({ fontSize: value })}
                            min={10}
                            max={100}
                        />
                        <ColorPicker
                            label={__("Color", "aurora-design-blocks")}
                            color={attributes.color}
                            onChangeComplete={(value) => setAttributes({ color: value.hex })}
                        />
                        <SelectControl
                            label={__("Font family", "aurora-design-blocks")}
                            value={attributes.fontFamily}
                            options={[
                                { label: 'Impact', value: "Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif" },
                                { label: 'Georgia', value: 'Georgia, serif' },
                                { label: 'Times New Roman', value: '"Times New Roman", serif' },
                                { label: 'Courier New', value: '"Courier New", monospace' },
                                { label: 'Verdana', value: 'Verdana, sans-serif' },
                                { label: 'Futura', value: '"Trebuchet MS", sans-serif' },
                                { label: 'Arial Black', value: '"Arial Black", sans-serif' }
                            ]}
                            onChange={(newFont) => setAttributes({ fontFamily: newFont })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <span className="guide-text">{__("Please enter the scrolling text.", "aurora-design-blocks")}</span>

                    <RichText
                        tagName="p"
                        value={attributes.content}
                        onChange={(content) => setAttributes({ content })}
                        placeholder={__("Enter text...", "aurora-design-blocks")}
                        style={{
                            fontSize: calculateFontSize(attributes.fontSize),
                            color: attributes.color,
                            fontFamily: attributes.fontFamily
                        }}
                    />

                </div>
            </>
        );
    },

    save: ({ attributes }) => {
        const blockProps = useBlockProps.save({ className: "aurora-design-blocks-text-flow-animation" });

        return (
            <div {...blockProps} style={{ fontSize: calculateFontSize(attributes.fontSize), color: attributes.color, fontFamily: attributes.fontFamily }}>
                <div className="loop_wrap">
                    <div>{attributes.content}&nbsp;</div>
                    <div>{attributes.content}&nbsp;</div>
                    <div>{attributes.content}&nbsp;</div>
                </div>
            </div>
        );
    }
});
