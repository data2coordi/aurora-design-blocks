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
import { PanelBody } from "@wordpress/components";
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

    return (
      <>
        <InspectorControls>
          <PanelBody
            title={__("Speech Bubble Direction", "aurora-design-blocks")}
            initialOpen={false}
          >
            <p>
              {__(
                "Double click the speech bubble in the editor to switch left/right.",
                "aurora-design-blocks"
              )}
            </p>
          </PanelBody>
        </InspectorControls>

        <div
          className={`${className} aurora-design-blocks-speech-bubble ${
            reverse
              ? "aurora-design-blocks-speech-bubble--reverse"
              : "aurora-design-blocks-speech-bubble--normal"
          }`}
          onDoubleClick={() => setAttributes({ reverse: !reverse })}
        >
          {imageUrl || imageCaption ? (
            <figure className="speech-bubble__image">
              <MediaUploadCheck>
                <MediaUpload
                  onSelect={onSelectImage}
                  allowedTypes={["image"]}
                  render={({ open }) => (
                    <img
                      src={imageUrl}
                      alt={imageAlt}
                      onClick={open}
                      style={{
                        width: "100%",
                        height: "100%",
                        objectFit: "cover",
                        cursor: "pointer",
                      }}
                    />
                  )}
                />
              </MediaUploadCheck>

              <RichText
                tagName="figcaption"
                className="speech-bubble__image-caption"
                value={imageCaption}
                onChange={(newCaption) =>
                  setAttributes({ imageCaption: newCaption })
                }
                placeholder={__("Enter caption here.", "aurora-design-blocks")}
              />
            </figure>
          ) : (
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
                marginBottom: "8px",
              }}
              onClick={() => {}}
            >
              <span>{__("Click to select image", "aurora-design-blocks")}</span>
            </div>
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
        className={`aurora-design-blocks-speech-bubble ${
          reverse
            ? "aurora-design-blocks-speech-bubble--reverse"
            : "aurora-design-blocks-speech-bubble--normal"
        }`}
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
