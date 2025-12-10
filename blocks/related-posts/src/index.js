import { registerBlockType } from "@wordpress/blocks";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import {
  PanelBody,
  RangeControl,
  ToggleControl,
  SelectControl,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import ServerSideRender from "@wordpress/server-side-render";

import "./style.css";
import "./editor.css";

registerBlockType("aurora-design-blocks/related-posts", {
  apiVersion: 2,

  attributes: {
    limit: { type: "number", default: 5 },
    showExcerpt: { type: "boolean", default: false },
    styleType: { type: "string", default: "list" },

    // ★ サムネイル表示 ON/OFF（ブロック側は boolean で保持）
    show_thumb: { type: "boolean", default: true },
  },

  edit: ({ attributes, setAttributes }) => {
    const { limit, showExcerpt, styleType, show_thumb } = attributes;

    const blockProps = useBlockProps({
      className: `adb-related-posts-block adb-style-${styleType}`,
    });

    return (
      <>
        <InspectorControls>
          <PanelBody
            title={__("Related Post Settings", "aurora-design-blocks")}
          >
            <RangeControl
              label={__("Number of posts to display", "aurora-design-blocks")}
              value={limit}
              onChange={(value) => setAttributes({ limit: value })}
              min={1}
              max={10}
              step={1}
            />

            <SelectControl
              label={__("Display Style", "aurora-design-blocks")}
              value={styleType}
              options={[
                {
                  label: __("List (Title only)", "aurora-design-blocks"),
                  value: "list",
                },
                {
                  label: __("Grid (Cards)", "aurora-design-blocks"),
                  value: "grid",
                },
              ]}
              onChange={(value) => setAttributes({ styleType: value })}
            />

            <ToggleControl
              label={__("Show Excerpt/Snippet", "aurora-design-blocks")}
              checked={showExcerpt}
              onChange={(value) => setAttributes({ showExcerpt: value })}
            />

            {/* ★ サムネイル表示 ON/OFF */}
            <ToggleControl
              label={__("Show Thumbnail", "aurora-design-blocks")}
              checked={show_thumb}
              onChange={(value) => setAttributes({ show_thumb: value })}
            />
          </PanelBody>
        </InspectorControls>

        <div {...blockProps}>
          <ServerSideRender
            block="aurora-design-blocks/related-posts"
            attributes={attributes}
          />
        </div>
      </>
    );
  },

  save: () => null, //    ★ SSR
});
