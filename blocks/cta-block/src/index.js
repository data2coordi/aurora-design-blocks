import { registerBlockType } from "@wordpress/blocks";
import {
  InnerBlocks,
  InspectorControls,
  useBlockProps,
} from "@wordpress/block-editor";
import { PanelBody, ToggleControl, SelectControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import "./style.css";
import "./editor.css"; // editor.css を読み込むことを明記

registerBlockType("aurora-design-blocks/cta-block", {
  attributes: {
    isFixed: { type: "boolean", default: true },
    position: { type: "string", default: "bottom-center" },
  },

  edit: ({ attributes, setAttributes }) => {
    const { isFixed, position } = attributes;

    // 'editor-guide' クラスを追加し、エディターでの視覚化に使用
    const blockProps = useBlockProps({
      className: `cta-block editor-guide ${isFixed ? "fixed" : ""} position-${position}`,
    });

    return (
      <>
        <InspectorControls>
          <PanelBody
            title={__("CTA Position Setteing", "aurora-design-blocks")}
          >
            <ToggleControl
              label={__("Make it a fixed float", "aurora-design-blocks")}
              checked={isFixed}
              onChange={(value) => setAttributes({ isFixed: value })}
            />
            <SelectControl
              label={__("CTA Position", "aurora-design-blocks")}
              value={position}
              options={[
                {
                  label: __("Left Top", "aurora-design-blocks"),
                  value: "top-left",
                },
                {
                  label: __("Center Top", "aurora-design-blocks"),
                  value: "top-center",
                },
                {
                  label: __("Right Top", "aurora-design-blocks"),
                  value: "top-right",
                },
                {
                  label: __("Left Bottom", "aurora-design-blocks"),
                  value: "bottom-left",
                },
                {
                  label: __("Center Bottom", "aurora-design-blocks"),
                  value: "bottom-center",
                },
                {
                  label: __("Right Bottom", "aurora-design-blocks"),
                  value: "bottom-right",
                },
                {
                  label: __("Left Center", "aurora-design-blocks"),
                  value: "center-left",
                },
                {
                  label: __("Right Center", "aurora-design-blocks"),
                  value: "center-right",
                },
              ]}
              onChange={(value) => setAttributes({ position: value })}
            />
          </PanelBody>
        </InspectorControls>

        <div {...blockProps}>
          {/* ★修正: ガイドメッセージを InnerBlocks の外側に常に表示 */}
          <div className="cta-editor-guide-message">
            {"[CTA Block] : "}
            {__(
              "The position can be set in the setting area on the right side of the screen. It will move to the set position when displayed as a website.",
              "aurora-design-blocks"
            )}
            <p style={{ fontSize: "12px", color: "#007cba", marginTop: "5px" }}>
              {__("Current position setting:", "aurora-design-blocks")}
              **{position}**
            </p>
          </div>

          <div className="cta-inner">
            <InnerBlocks
              // InnerBlocksの入力エリアを常に表示する
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
      className: `cta-block ${isFixed ? "fixed" : ""} position-${position}`,
    });

    return (
      <div {...blockProps}>
        <div className="cta-inner">
          <InnerBlocks.Content />
        </div>
      </div>
    );
  },
});
