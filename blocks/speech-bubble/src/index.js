import "./editor.css";
import "./style.css";

import { registerBlockType } from "@wordpress/blocks";
import {
  InspectorControls,
  RichText,
  MediaUpload,
  MediaUploadCheck,
  useBlockProps,
} from "@wordpress/block-editor";
import { PanelBody, ToggleControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useEffect } from "@wordpress/element";

registerBlockType("aurora-design-blocks/speech-bubble", {
  edit: (props) => {
    const {
      attributes: {
        content,
        imageUrl,
        imageAlt,
        imageCaption,
        reverse,
        style = {},
      },
      setAttributes,
      className,
    } = props;

    const backgroundColor = style?.color?.background;
    const textColor = style?.color?.text;

    // 初期色設定
    useEffect(() => {
      if (!backgroundColor && !textColor) {
        setAttributes({
          style: { color: { background: "#00aabb", text: "#ffffff" } },
        });
      }
    }, [backgroundColor, textColor]);

    const onSelectImage = (media) => {
      setAttributes({
        imageUrl: media.url,
        imageAlt: media.alt || __("faceimage", "aurora-design-blocks"),
      });
    };

    const contentBlockProps = useBlockProps({
      className: "speech-bubble__content",
      style: { backgroundColor, color: textColor },
    });

    // ブロック内クリックで画像をセットするUI
    const renderImage = () => (
      <MediaUploadCheck>
        <MediaUpload
          onSelect={onSelectImage}
          allowedTypes={["image"]}
          render={({ open }) => (
            <div
              className="speech-bubble__image-wrapper"
              style={{
                cursor: "pointer",
                width: "150px",
                height: "150px",
                border: "1px dashed #ccc",
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                overflow: "hidden",
              }}
              onClick={open}
            >
              {imageUrl ? (
                <img
                  src={imageUrl}
                  alt={imageAlt}
                  style={{ width: "100%", height: "100%", objectFit: "cover" }}
                />
              ) : (
                <span>
                  {__("Click to select image", "aurora-design-blocks")}
                </span>
              )}
            </div>
          )}
        />
      </MediaUploadCheck>
    );

    return (
      <>
        <InspectorControls>
          <PanelBody
            title={__("Layout settings", "aurora-design-blocks")}
            initialOpen={false}
          >
            <ToggleControl
              label={__(
                "Reverse the positions of the image and speech bubble.",
                "aurora-design-blocks"
              )}
              checked={reverse}
              onChange={(newVal) => setAttributes({ reverse: newVal })}
            />
          </PanelBody>
        </InspectorControls>

        <div
          className={`${className} aurora-design-blocks-speech-bubble ${reverse ? "aurora-design-blocks-speech-bubble--reverse" : "aurora-design-blocks-speech-bubble--normal"}`}
        >
          {renderImage()}
          {imageCaption && (
            <RichText
              tagName="figcaption"
              className="speech-bubble__image-caption"
              onChange={(newCaption) =>
                setAttributes({ imageCaption: newCaption })
              }
              value={imageCaption}
              placeholder={__("Enter caption here.", "aurora-design-blocks")}
            />
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
        style = {},
      },
    } = props;
    const backgroundColor = style?.color?.background;
    const textColor = style?.color?.text;

    const contentBlockProps = useBlockProps.save({
      className: "speech-bubble__content",
      style: { backgroundColor, color: textColor },
    });

    return (
      <div
        className={`aurora-design-blocks-speech-bubble ${reverse ? "aurora-design-blocks-speech-bubble--reverse" : "aurora-design-blocks-speech-bubble--normal"}`}
      >
        {imageUrl && (
          <figure className="speech-bubble__image">
            <img src={imageUrl} alt={imageAlt} />
            {imageCaption && (
              <RichText.Content
                tagName="figcaption"
                className="speech-bubble__image-caption"
                value={imageCaption}
              />
            )}
          </figure>
        )}
        <div {...contentBlockProps}>
          <RichText.Content tagName="p" value={content} />
        </div>
      </div>
    );
  },
});
