import { registerBlockType } from "@wordpress/blocks";
import {
  InnerBlocks,
  RichText,
  InspectorControls,
  useBlockProps,
} from "@wordpress/block-editor";
import { PanelBody } from "@wordpress/components";
import { Fragment, useState } from "@wordpress/element";
import { useSelect } from "@wordpress/data";

import "./editor.css";
import "./style.css";

import { __ } from "@wordpress/i18n";

/**
 * ---------------------------------------------------
 * 子ブロック「タブ」 (aurora-design-blocks/tab)
 * ---------------------------------------------------
 * ★重要: ここでは RichText と InnerBlocks を使ってコンテンツを保持するだけ。
 * 親ブロックのロジックは一切含めない。
 */
registerBlockType("aurora-design-blocks/tab", {
  title: __("Tab", "aurora-design-blocks"),
  parent: ["aurora-design-blocks/tab-block"],
  icon: "screenoptions",
  category: "layout",
  attributes: {
    tabTitle: {
      type: "string",
      source: "html",
      selector: ".tab-title h4",
      default: "",
    },
  },
  edit: (props) => {
    const { attributes, setAttributes } = props;
    const blockProps = useBlockProps({ className: "tab" });

    return (
      // 子ブロックの edit はシンプルに、タイトルとコンテンツ（InnerBlocks）のみを返す
      <div {...blockProps}>
        <div className="tab-title">
          <RichText
            tagName="h4"
            placeholder={__("Tab title...", "aurora-design-blocks")}
            value={attributes.tabTitle}
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
    const { attributes } = props;
    const blockProps = useBlockProps.save({
      className: "wp-block-aurora-design-blocks-tab tab",
    });

    return (
      <div {...blockProps}>
        <div className="tab-title">
          <RichText.Content tagName="h4" value={attributes.tabTitle} />
        </div>
        <div className="tab-content">
          <InnerBlocks.Content />
        </div>
      </div>
    );
  },
});

/**
 * ---------------------------------------------------
 * 親ブロック「タブブロック」 (aurora-design-blocks/tab-block)
 * ---------------------------------------------------
 */
registerBlockType("aurora-design-blocks/tab-block", {
  // title, icon, category は block.json に任せる

  edit: (props) => {
    const { clientId } = props;
    const [activeTabIndex, setActiveTabIndex] = useState(0);

    // データ取得の最適化（クラッシュ回避のため）
    const tabBlocks = useSelect(
      (select) => {
        const block = select("core/block-editor").getBlock(clientId);
        if (!block || !block.innerBlocks) return [];

        return block.innerBlocks.map((innerBlock) => ({
          clientId: innerBlock.clientId,
          tabTitle: innerBlock.attributes?.tabTitle || "",
        }));
      },
      [clientId]
    );

    // ナビゲーションの作成
    const tabTitles = tabBlocks.map((blockData, index) => {
      const title =
        blockData.tabTitle ||
        `${__("Tab", "aurora-design-blocks")} ${index + 1}`;

      return (
        <li
          key={blockData.clientId}
          className={index === activeTabIndex ? "active" : ""}
          onClick={() => setActiveTabIndex(index)}
          onMouseDown={(e) => e.stopPropagation()}
        >
          {title}
        </li>
      );
    });

    // 親ブロックの props に data-active-tab を付与して CSS で制御
    const blockProps = useBlockProps({
      className: "aurora-design-blocks-tabs-block",
      "data-active-tab": activeTabIndex,
    });

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__("Tab setting", "aurora-design-blocks")}>
            {/* 必要に応じて設定を追加 */}
          </PanelBody>
        </InspectorControls>

        <div {...blockProps}>
          {/* タブナビゲーション */}
          <ul className="tabs-navigation">{tabTitles}</ul>

          {/* コンテンツエリア */}
          <div className="tab-contents-editor">
            {/* InnerBlocks は単純に置くのみ（子ブロックのレンダリング） */}
            <InnerBlocks
              allowedBlocks={["aurora-design-blocks/tab"]}
              template={[["aurora-design-blocks/tab", {}]]}
              // renderAppenderはここでは不要
            />

            {/* ★修正: ブロック追加ボタンを明示的に配置 (Appender) */}
            <InnerBlocks.ButtonBlockAppender
              allowedBlocks={["aurora-design-blocks/tab"]}
              // 新しいタブが追加されたら、そのタブをアクティブにする
              onInsert={() => setActiveTabIndex(tabBlocks.length)}
            />
          </div>
        </div>
      </Fragment>
    );
  },

  save: () => {
    const blockProps = useBlockProps.save({
      className: "aurora-design-blocks-tabs",
    });

    return (
      <div {...blockProps}>
        <InnerBlocks.Content />
      </div>
    );
  },
});

// フロントエンドJS (initializeTabsNavigation) は変更なし
// ...
