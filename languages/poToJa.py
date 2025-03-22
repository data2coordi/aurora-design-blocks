import re

# 翻訳テーブル（/mnt/data/aurora-design-blocks.pot 内の 99 ユニークな msgid をカバー）
translation_table = {
"aurora-design-blocks": "aurora-design-blocks",
"http://color.toshidayurika.com/": "http://color.toshidayurika.com/",
"Description": "説明",
"Underscores.me": "Underscores.me",
"Oops! That page can&rsquo;t be found.": "おっと！そのページは見つかりませんでした。",
"It looks like nothing was found at this location. Maybe try one of the links below or a search?": "この場所には何も見つかりませんでした。下のリンクを試すか、検索してみてください。",
"Most Used Categories": "最も使用されているカテゴリー",
"Try looking in the monthly archives. %1$s": "月別アーカイブを確認してみてください。%1$s",
"One thought on &ldquo;%1$s&rdquo;": "「%1$s」への1件のコメント",
"%1$s thought on &ldquo;%2$s&rdquo;": "%1$s件のコメント「%2$s」",
"Comments are closed.": "コメントは受け付けていません。",
"https://wordpress.org/": "https://wordpress.org/",
"Proudly powered by %s": "%s によって誇りを持って運営されています。",
"Theme: %1$s by %2$s.": "テーマ: %1$s（%2$s 制作）。",
"Primary": "メイン",
"Sidebar1": "サイドバー1",
"Add widgets here.": "ここにウィジェットを追加してください。",
"Sidebar2": "サイドバー2",
"Skip to content": "コンテンツへスキップ。",
"Top Header:[Image settings]": "トップヘッダー：[画像設定]。",
"Top Header:[Select - Slider or Image]": "トップヘッダー：[選択 - スライダーまたは画像]。",
"Display Slider or Image": "スライダーまたは画像を表示。",
"Slider": "スライダー。",
"Image": "画像。",
"Effect": "エフェクト。",
"Fade": "フェード。",
"Slide": "スライド。",
"None": "なし。",
"Slider Fade Duration (seconds)": "スライダーのフェード時間（秒）。",
"Slider Text Font": "スライダーテキストのフォント。",
"yu gothic": "游ゴシック。",
"yu mincho": "游明朝。",
"Top Header:[Slider Settings]": "トップヘッダー：[スライダー設定]。",
"Sidebar": "サイドバー。",
"Right": "右。",
"Left": "左。",
"Bottom": "下。",
"Sidebar Settings": "サイドバー設定。",
"Base color pattern": "基本カラー設定。",
"The base color pattern you select will be reflected throughout the site.": "選択した基本カラー設定はサイト全体に適用されます。",
"Footer Settings": "フッター設定。",
"Footer": "フッター。",
"TOC Visibility": "目次の表示設定。",
"Hide TOC": "目次を非表示。",
"%s": "%s。",
"by %s": "%s 作成。",
", ": "、",
"Posted in %1$s": "%1$s に投稿。",
"Tagged %1$s": "%1$s タグ付き。",
"Leave a Comment<span class=\"screen-reader-text\"> on %s</span>": "%s にコメントを残す。",
"Edit <span class=\"screen-reader-text\">%s</span>": "%s を編集。",
"Search Results for: %s": "検索結果: %s。",
"Nothing Found": "何も見つかりませんでした。",
"Ready to publish your first post? <a href=\"%1$s\">Get started here</a>.": "最初の記事を公開しますか？ <a href=\"%1$s\">ここから始めましょう</a>。",
"Sorry, but nothing matched your search terms. Please try again with some different keywords.": "申し訳ありませんが、ご指定の検索条件に一致するものはありませんでした。別のキーワードで試してください。",
"It seems we can’t find what you’re looking for. Perhaps searching can help.": "お探しのものが見つかりませんでした。検索をお試しください。",
"Pages:": "ページ:。",
"aaaaaaaaaEdit <span class=\"screen-reader-text\">%s</span>": "aaaaaaaaa%s を編集。",
"Continue reading<span class=\"screen-reader-text\"> \"%s\"</span>": "続きを読む「%s」。",
"Cover Settings": "カバー設定。",
"Use Article Width for Inner Content": "内部コンテンツに記事幅を適用。",
"Overlay Opacity (-100 for bright, 100 for dark)": "オーバーレイの透明度（-100 で明るく、100 で暗く）。",
"Change Background Image": "背景画像を変更。",
"Upload Background Image": "背景画像をアップロード。",
"Remove Background Image": "背景画像を削除。",
"Image setting": "画像設定。",
"Delete image": "画像を削除。",
"faceimage": "顔画像。",
"Select image": "画像を選択。",
"layout setting": "レイアウト設定。",
"Reverse the positions of the image and speech bubble.": "画像と吹き出しの位置を入れ替える。",
"Enter caption here.": "キャプションを入力してください。",
"Enter message here.": "メッセージを入力してください。",
"Tab": "タブ。",
"Tab title...": "タブのタイトル...",
"Tab setting": "タブ設定。",
"Tab switching is reflected when the website is displayed.": "ウェブサイト表示時にタブの切り替えが反映されます。",
"Text setting": "テキスト設定。",
"Font size": "フォントサイズ。",
"Color": "カラー。",
"Font family": "フォントファミリー。",
"Enter text...": "テキストを入力...",
"Color Settings": "カラー設定。",
"Background Color": "背景色。",
"Text Color": "テキストの色。",
"Overlay Color": "オーバーレイの色。",
"【aurora-design-blocks】Custom Cover": "【aurora-design-blocks】カスタムカバー。",
"A custom cover block with fixed full-width outer container and inner content width selectable as article width or full width.": "固定のフル幅外部コンテナを持ち、内部コンテンツの幅を記事幅またはフル幅として選択できるカスタムカバーブロック。",
"[aurora-design-blocks]slider block": "[aurora-design-blocks]スライダーブロック。",
"A block that slides multiple contents.": "複数のコンテンツをスライドさせるブロック。",
"[aurora-design-blocks]speech bubble": "[aurora-design-blocks]吹き出し。",
"A speech bubble block where you can set images and text, and customize the background and text colors.": "画像とテキストを設定し、背景色やテキスト色をカスタマイズできる吹き出しブロック。",
"[aurora-design-blocks]Tab switching feature.": "[aurora-design-blocks]タブ切り替え機能。",
"A feature that allows switching between multiple contents using tabs.": "タブを使用して複数のコンテンツを切り替える機能。",
"【aurora-design-blocks】text flow animation": "【aurora-design-blocks】テキストフローアニメーション。",
"Hello World Block": "Hello World ブロック。",
}

# .pot ファイル全体を行単位で読み込み（約430行以上ある前提）
with open("./aurora-design-blocks.pot", "r", encoding="utf-8") as f:
    lines = f.readlines()

result_lines = []
current_msgid = ""
i = 0

while i < len(lines):
    line = lines[i]
    # msgid ブロックの処理（複数行にまたがる場合を連結）
    if line.startswith("msgid"):
        m = re.match(r'msgid\s+"(.*)"', line)
        current_msgid = m.group(1) if m else ""
        result_lines.append(line)
        i += 1
        # 連続する msgid の継続行を連結
        while i < len(lines) and lines[i].startswith('"'):
            m = re.match(r'"(.*)"', lines[i])
            if m:
                current_msgid += m.group(1)
            result_lines.append(lines[i])
            i += 1
        continue

    # msgstr ブロックの処理：翻訳テーブルの内容で上書き
    elif line.startswith("msgstr"):
        translation = translation_table.get(current_msgid, current_msgid)
        result_lines.append(f'msgstr "{translation}"\n')
        i += 1
        # 継続する msgstr の行はスキップ
        while i < len(lines) and lines[i].startswith('"'):
            i += 1
        continue

    else:
        result_lines.append(line)
        i += 1

# 結果を新しい .po ファイルとして保存
translated_file_path = "./ja.po"
with open(translated_file_path, "w", encoding="utf-8") as f:
    f.writelines(result_lines)

print("新しい翻訳済みファイル:", translated_file_path)

