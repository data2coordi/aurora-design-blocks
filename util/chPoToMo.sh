######## ja.poに英語に対応する日本語を記載する。(CHATGpt活用可能）aurora-design-blocks s
## delete
rm -f ../languages/*.mo
rm -f ../languages/*.json

## mo生成
src=../languages/aurora-design-blocks-ja.po
tgt=../languages/aurora-design-blocks-ja.mo
msgfmt ${src} -o ${tgt}

## javascript用json生成生成
src=../languages
wp i18n  make-json ${src} --allow-root --no-purge
#exit


######## ja.poに英語に対応する日本語を記載する。(CHATGpt活用可能）aurora-design-blocks e