import { registerBlockType } from "@wordpress/blocks";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import {
  PanelBody,
  RangeControl,
  ToggleControl,
  SelectControl,
  Spinner, // ★ ロード表示用に追加
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useSelect } from "@wordpress/data"; // ★投稿ID取得用に追加
import { useState, useEffect } from "@wordpress/element"; // ★状態管理用に追加
import apiFetch from "@wordpress/api-fetch"; // ★REST APIコール用に追加

import "./style.css";
import "./editor.css";

registerBlockType("aurora-design-blocks/related-posts", {
  attributes: {
    limit: { type: "number", default: 5 },
    showExcerpt: { type: "boolean", default: false },
    styleType: { type: "string", default: "list" }, // list or grid
  },

  edit: ({ attributes, setAttributes }) => {
    const { limit, showExcerpt, styleType } = attributes;

    // ★ 状態管理フックの追加: 取得データとロード状態
    const [relatedPosts, setRelatedPosts] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    // ★ 修正後の投稿ID取得ロジック: データストアの存在を安全に確認
    const currentPostId = useSelect((select) => {
      // 'core/editor' (投稿編集) または 'core/edit-post' (サイトエディター/テンプレート) ストアを使用
      const editorStore = select("core/editor") || select("core/edit-post");

      // ストアが存在し、かつ getCurrentPostId メソッドが利用可能かを確認
      if (editorStore && editorStore.getCurrentPostId) {
        return editorStore.getCurrentPostId();
      }
      // 投稿IDが取得できない場合は null を返す
      return null;
    }, []);

    // ★ データ取得ロジック (useEffect): 依存データが変わるたびにAPIを叩く
    useEffect(() => {
      // currentPostId が null の場合（エディターコンテキスト外）はAPIコールせずに終了
      if (!currentPostId) {
        setIsLoading(false);
        setRelatedPosts([]); // リストを空にする
        return;
      }

      setIsLoading(true);
      setRelatedPosts(null);

      // PHPで定義されたカスタムREST APIエンドポイントを呼び出す
      // エンドポイント例: /aurora-design-blocks/v1/related-posts?post_id=X&limit=Y
      apiFetch({
        path: `/aurora-design-blocks/v1/related-posts?post_id=${currentPostId}&limit=${limit}&excerpt=${showExcerpt}`,
      })
        .then((posts) => {
          setRelatedPosts(posts);
          setIsLoading(false);
        })
        .catch((error) => {
          console.error("Failed to fetch related posts:", error);
          setRelatedPosts([]); // エラー時は空リスト
          setIsLoading(false);
        });
    }, [currentPostId, limit, showExcerpt]); // 依存配列: ID, limit, Excerptが変更されたら再実行

    const blockProps = useBlockProps({
      className: `adb-related-posts-block adb-style-${styleType}`,
    });

    // ★ エディター内での動的な表示部分
    const renderRelatedList = () => {
      // 投稿IDが取得できず、ロードも完了している（つまりエディターコンテキスト外）場合のメッセージ
      if (!currentPostId && !isLoading) {
        return (
          <div className="adb-related-guide adb-context-error">
            {__("【相互参照型関連記事】", "aurora-design-blocks")}
            <p>
              {__(
                "注意: このブロックは投稿/ページ編集画面でのみデータプレビューを表示できます。",
                "aurora-design-blocks"
              )}
            </p>
          </div>
        );
      }

      if (isLoading) {
        return (
          <div className="adb-related-loading">
            <Spinner />
            <p>
              {__(
                "関連記事をデータベースから取得中...",
                "aurora-design-blocks"
              )}
            </p>
          </div>
        );
      }

      if (relatedPosts === null || relatedPosts.length === 0) {
        return (
          <div className="adb-related-guide">
            {__("【相互参照型関連記事】", "aurora-design-blocks")}
            <p>
              {__(
                "現在のリンク構造に基づき、関連性の高い記事は見つかりませんでした。",
                "aurora-design-blocks"
              )}
            </p>
            <p>
              {__(
                "本文のリンクを確認するか、記事を保存して再スキャンしてください。",
                "aurora-design-blocks"
              )}
            </p>
          </div>
        );
      }

      // 取得したデータをリスト表示 (エディタープレビュー)
      return (
        <div className={`adb-related-output adb-style-${styleType}`}>
          <p>
            <strong>
              {__("プレビュー (DBデータに基づく):", "aurora-design-blocks")}
            </strong>
            {currentPostId && (
              <span style={{ fontSize: "0.8em", opacity: 0.7 }}>
                {" "}
                (ID: {currentPostId})
              </span>
            )}
          </p>
          <ul>
            {relatedPosts.map((post) => (
              <li key={post.id}>
                <a href={post.link} target="_blank" rel="noopener noreferrer">
                  {post.title}
                </a>
                {post.score && (
                  <span className="adb-score"> (スコア: {post.score})</span>
                )}
                {showExcerpt && post.excerpt && (
                  <p className="adb-excerpt">{post.excerpt}</p>
                )}
              </li>
            ))}
          </ul>
        </div>
      );
    };

    return (
      <>
        <InspectorControls>
          {/* ... (PanelBody, RangeControl, SelectControl, ToggleControlは変更なし) ... */}
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
          </PanelBody>
        </InspectorControls>

        <div {...blockProps}>
          {renderRelatedList()} {/* ★ 動的な表示をレンダリング */}
        </div>
      </>
    );
  },

  // save関数は不要 （PHPのrender_callbackで動的コンテンツを挿入するため）

  // save: ({ attributes }) => {
  //   const { limit, showExcerpt, styleType } = attributes;
  //   const blockProps = useBlockProps.save({
  //     className: `adb-related-posts-block adb-style-${styleType}`,
  //     "data-limit": limit,
  //     "data-show-excerpt": showExcerpt ? "true" : "false",
  //   });

  //   return <div {...blockProps}></div>;
  // },
});
