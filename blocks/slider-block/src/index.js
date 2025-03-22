import { registerBlockType } from '@wordpress/blocks';
import {
    InnerBlocks,
    InspectorControls,
    useBlockProps
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import './editor.css';
import './style.css';

registerBlockType('aurora-design-blocks/slider-block', {
    edit: (props) => {
        const blockProps = useBlockProps({
            className: 'editorBlockSliders'
        });

        return (
            <Fragment>
                {/*
                <InspectorControls>
                    <PanelBody title=__("slider setting") initialOpen={true}>
                        { }
                    </PanelBody>
                </InspectorControls>
                */}
                <div {...blockProps}>
                    <div className="blockSliders-navigation-editor">
                        <p>{__("Please create multiple pieces of content. They will be displayed in a slide format when viewed as a website.", "aurora-design-blocks")}</p>
                    </div>
                    <div className="blockSliders-content-editor">
                        <InnerBlocks
                        />
                    </div>
                </div>
            </Fragment>
        );
    },
    save: () => {
        const blockProps = useBlockProps.save({ className: 'blockSliders' });

        return (
            <div {...blockProps}>
                <div className="blockSliders-content">
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
