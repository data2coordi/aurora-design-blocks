import { registerBlockType } from '@wordpress/blocks';
import {
    InnerBlocks,
    RichText,
    InspectorControls,
    useBlockProps
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

import './editor.css';
import './style.css';

import { __ } from '@wordpress/i18n';
/**
 * 子ブロック「タブ」の登録
 */
registerBlockType('integlight/tab', {
    title: __("Tab", "integlight"),
    parent: ['integlight/tab-block'],
    icon: 'screenoptions',
    category: 'layout',
    attributes: {
        tabTitle: {
            type: 'string',
            source: 'html',
            selector: '.tab-title h4',
            default: '' // デフォルト値を空にする
        }
    },
    edit: (props) => {
        const { attributes: { tabTitle }, setAttributes, className } = props;
        const blockProps = useBlockProps({ className: 'tab' });
        return (
            <div {...blockProps}>
                <div className="tab-title">
                    <RichText
                        tagName="h4"
                        placeholder={__("Tab title...", "integlight")}
                        value={tabTitle}
                        onChange={(value) => setAttributes({ tabTitle: value })}
                    />
                </div>
                <div className="tab-content">
                    <InnerBlocks />
                </div>
            </div>
        );
    },
    save: (props) => {
        const { attributes: { tabTitle } } = props;
        const blockProps = useBlockProps.save({ className: 'wp-block-integlight-tab tab' });

        return (
            <div {...blockProps}>
                <div className="tab-title">
                    <RichText.Content tagName="h4" value={tabTitle} />
                </div>
                <div className="tab-content">
                    <InnerBlocks.Content />
                </div>
            </div >
        );
    }
});






/**
 * 親ブロック「タブブロック」の登録
 */
registerBlockType('integlight/tab-block', {
    edit: (props) => {


        const contentBlockProps = useBlockProps({
            className: 'integlight-tabs-block'
        });


        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title={__("Tab setting", "integlight")} initialOpen={true}>
                        {/* ここにインスペクター用の設定項目を追加可能 */}
                    </PanelBody>
                </InspectorControls>
                <div {...contentBlockProps}>
                    <div className="tabs-navigation-editor">
                        <p>{__("Tab switching is reflected when the website is displayed.", "integlight")}</p>
                    </div>
                    <div>
                        <InnerBlocks
                            allowedBlocks={['integlight/tab']}
                            template={[['integlight/tab', {}]]}
                            templateLock={false}
                            renderAppender={InnerBlocks.ButtonBlockAppender}
                        />
                    </div>
                </div>
            </Fragment>
        );
    },
    save: () => {
        const blockProps = useBlockProps.save({ className: 'integlight-tabs' }); // 修正

        return (
            <div {...blockProps}>

                <div >
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
