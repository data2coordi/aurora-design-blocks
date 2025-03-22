import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InspectorControls,
    InnerBlocks,
    MediaUpload,
    MediaUploadCheck
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl, Button, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './style.css';
import './editor.css';

// ユーザーが選んだ透明度に応じて、オーバーレイの色を決定する関数
// dimRatio が正なら黒で暗く、負なら白で明るくなる
const getOverlayColor = (dimRatio) => {
    if (dimRatio >= 0) {
        return `rgba(0, 0, 0, ${dimRatio / 100})`;
    } else {
        return `rgba(255, 255, 255, ${Math.abs(dimRatio) / 100})`;
    }
};

registerBlockType('integlight/custom-cover', {
    edit: ({ attributes, setAttributes }) => {
        const { innerWidthArticle, url, id, alt, focalPoint, dimRatio } = attributes;

        // ブロック本体には背景画像のみを設定（背景色／グラデーションは、supports によりクラスとして出力される）
        const blockProps = useBlockProps({
            className: 'wp-block-integlight-custom-cover alignfull',
            style: {
                backgroundImage: url ? `url(${url})` : undefined,
                backgroundPosition: url ? `${focalPoint.x * 100}% ${focalPoint.y * 100}%` : undefined,
                backgroundSize: 'cover',
                position: 'relative'
            }
        });

        const innerClass = innerWidthArticle ? 'inner-article' : 'inner-full';

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Cover Settings', 'integlight')}>
                        <ToggleControl
                            label={__('Use Article Width for Inner Content', 'integlight')}
                            checked={innerWidthArticle}
                            onChange={() => setAttributes({ innerWidthArticle: !innerWidthArticle })}
                        />
                        <RangeControl
                            label={__('Overlay Opacity (-100 for bright, 100 for dark)', 'integlight')}
                            value={dimRatio}
                            onChange={(newDimRatio) => setAttributes({ dimRatio: newDimRatio })}
                            min={-100}
                            max={100}
                        />
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={(media) =>
                                    setAttributes({
                                        url: media.url,
                                        id: media.id,
                                        alt: media.alt
                                    })
                                }
                                allowedTypes={['image']}
                                value={id}
                                render={({ open }) => (
                                    <Button onClick={open} isPrimary>
                                        {!url
                                            ? __('Upload Background Image', 'integlight')
                                            : __('Change Background Image', 'integlight')}
                                    </Button>
                                )}
                            />
                        </MediaUploadCheck>
                        {url && (
                            <Button
                                onClick={() => setAttributes({ url: '', id: undefined, alt: '' })}
                                isSecondary
                                style={{ marginTop: '10px' }}
                            >
                                {__('Remove Background Image', 'integlight')}
                            </Button>
                        )}
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    {/* オーバーレイ要素：getOverlayColor(dimRatio) により色が決まる */}
                    <div
                        className="cover-overlay"
                        style={{
                            background: getOverlayColor(dimRatio),
                            position: 'absolute',
                            top: 0,
                            left: 0,
                            right: 0,
                            bottom: 0,
                            zIndex: 1,
                            pointerEvents: 'none'
                        }}
                    ></div>
                    <div className={`inner-container ${innerClass}`} style={{ position: 'relative', zIndex: 2 }}>
                        <InnerBlocks />
                    </div>
                </div>
            </>
        );
    },
    save: ({ attributes }) => {
        const { innerWidthArticle, url, focalPoint, dimRatio } = attributes;

        const blockProps = useBlockProps.save({
            className: 'wp-block-integlight-custom-cover alignfull',
            style: {
                backgroundImage: url ? `url(${url})` : undefined,
                backgroundPosition: url ? `${focalPoint.x * 100}% ${focalPoint.y * 100}%` : undefined,
                backgroundSize: 'cover',
                position: 'relative'
            }
        });

        const innerClass = innerWidthArticle ? 'inner-article' : 'inner-full';

        return (
            <div {...blockProps}>
                <div
                    className="cover-overlay"
                    style={{
                        background: getOverlayColor(dimRatio),
                        position: 'absolute',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        zIndex: 1,
                        pointerEvents: 'none'
                    }}
                ></div>
                <div className={`inner-container ${innerClass}`} style={{ position: 'relative', zIndex: 2 }}>
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
