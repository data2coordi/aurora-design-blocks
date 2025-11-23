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
registerBlockType('aurora-design-blocks/tab', {
    title: __("Tab", "aurora-design-blocks"),
    parent: ['aurora-design-blocks/tab-block'],
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
                        placeholder={__("Tab title...", "aurora-design-blocks")}
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
        const blockProps = useBlockProps.save({ className: 'wp-block-aurora-design-blocks-tab tab' });

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
registerBlockType('aurora-design-blocks/tab-block', {
    edit: (props) => {


        const contentBlockProps = useBlockProps({
            className: 'aurora-design-blocks-tabs-block'
        });


        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title={__("Tab setting", "aurora-design-blocks")} initialOpen={true}>
                        {/* ここにインスペクター用の設定項目を追加可能 */}
                    </PanelBody>
                </InspectorControls>
                <div {...contentBlockProps}>
                    <div className="tabs-navigation-editor">
                        <p>{__("Tab switching is reflected when the website is displayed.", "aurora-design-blocks")}</p>
                    </div>
                    <div>
                        <InnerBlocks
                            allowedBlocks={['aurora-design-blocks/tab']}
                            template={[['aurora-design-blocks/tab', {}]]}
                            templateLock={false}
                            renderAppender={InnerBlocks.ButtonBlockAppender}
                        />
                    </div>
                </div>
            </Fragment>
        );
    },
    save: () => {
        const blockProps = useBlockProps.save({ className: 'aurora-design-blocks-tabs' }); // 修正

        return (
            <div {...blockProps}>

                <div >
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
