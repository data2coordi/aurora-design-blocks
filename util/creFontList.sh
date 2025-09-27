
pyftsubset ../css/webfonts/fa-solid-900.woff2 \
  --unicodes-file=awesomeFontList.txt \
  --flavor=woff2 \
  --output-file=fa-solid-900-subset.woff2



exit

cssFileName=../css/src/awesome-all.css

grep -o '\\f[0-9a-f]\{3,4\}' ${cssFileName} | sort -u


exit

pyftsubset ../css/webfonts/fa-solid-900.woff2 \
  --unicodes-file=awesomeFontList.txt \
  --flavor=woff2 \
  --output-file=fa-solid-900-subset.woff2