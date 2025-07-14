import './editor.css';
import './style.css';

import { registerBlockType } from '@wordpress/blocks';
import {
    InspectorControls,
    RichText,
    MediaUpload,
    MediaUploadCheck,
    useBlockProps
} from '@wordpress/block-editor';
import { PanelBody, Button, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';

registerBlockType('aurora-design-blocks/speech-bubble', {
    edit: (props) => {
        const {
            attributes: {
                content,
                imageUrl,
                imageAlt,
                imageCaption,
                reverse,
                style = {}
            },
            setAttributes,
            className
        } = props;

        const backgroundColor = style?.color?.background;
        const textColor = style?.color?.text;

        // 🔽 初期状態で色が未設定なら、初期色を設定
        useEffect(() => {
            if (!backgroundColor && !textColor) {
                setAttributes({
                    style: {
                        color: {
                            background: '#00aabb',
                            text: '#ffffff'
                        }
                    }
                });
            }
        }, []);

        const onSelectImage = (media) => {
            setAttributes({
                imageUrl: media.url,
                imageAlt: media.alt || __('faceimage', 'aurora-design-blocks')
            });
        };

        const contentBlockProps = useBlockProps({
            className: 'speech-bubble__content',
            style: {
                backgroundColor,
                color: textColor
            }
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__("Image setting", "aurora-design-blocks")} initialOpen={true}>
                        {imageUrl ? (
                            <div>
                                <img src={imageUrl} alt={imageAlt} style={{ width: '100%' }} />
                                <Button
                                    onClick={() => setAttributes({ imageUrl: '', imageAlt: '' })}
                                    isLink
                                    isDestructive
                                >
                                    {__("Change image", "aurora-design-blocks")}
                                </Button>
                            </div>
                        ) : (
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={onSelectImage}
                                    allowedTypes={['image']}
                                    render={({ open }) => (
                                        <Button onClick={open} isPrimary>
                                            {__("Select image", "aurora-design-blocks")}
                                        </Button>
                                    )}
                                />
                            </MediaUploadCheck>
                        )}
                    </PanelBody>
                    <PanelBody title={__("layout setting", "aurora-design-blocks")} initialOpen={false}>
                        <ToggleControl
                            label={__("Reverse the positions of the image and speech bubble.", "aurora-design-blocks")}
                            checked={reverse}
                            onChange={(newVal) => setAttributes({ reverse: newVal })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div className={`${className} wp-block aurora-design-blocks-speech-bubble ${reverse ? "aurora-design-blocks-speech-bubble--reverse" : "aurora-design-blocks-speech-bubble--normal"}`}>
                    {imageUrl && (
                        <figure className="speech-bubble__image">
                            <img src={imageUrl} alt={imageAlt} />
                            <RichText
                                tagName="figcaption"
                                className="speech-bubble__image-caption"
                                onChange={(newCaption) => setAttributes({ imageCaption: newCaption })}
                                value={imageCaption}
                                placeholder={__("Enter caption here.", "aurora-design-blocks")}
                            />
                        </figure>
                    )}
                    <div {...contentBlockProps}>
                        <RichText
                            tagName="p"
                            onChange={(newContent) => setAttributes({ content: newContent })}
                            value={content}
                            placeholder={__("Enter message here.", "aurora-design-blocks")}
                        />
                    </div>
                </div>
            </>
        );
    },

    save: (props) => {
        const {
            attributes: {
                content,
                imageUrl,
                imageAlt,
                imageCaption,
                reverse,
                style = {}
            }
        } = props;

        const backgroundColor = style?.color?.background;
        const textColor = style?.color?.text;

        const contentBlockProps = useBlockProps.save({
            className: 'speech-bubble__content',
            style: {
                backgroundColor,
                color: textColor
            }
        });

        return (
            <div className={`aurora-design-blocks-speech-bubble ${reverse ? "aurora-design-blocks-speech-bubble--reverse" : "aurora-design-blocks-speech-bubble--normal"}`}>
                {imageUrl && (
                    <figure className="speech-bubble__image">
                        <img src={imageUrl} alt={imageAlt} />
                        <RichText.Content
                            tagName="figcaption"
                            className="speech-bubble__image-caption"
                            value={imageCaption}
                        />
                    </figure>
                )}
                <div {...contentBlockProps}>
                    <RichText.Content tagName="p" value={content} />
                </div>
            </div>
        );
    }
});
