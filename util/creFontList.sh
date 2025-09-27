cssFileName=../css/src/awesome-all.css

grep -o '\\f[0-9a-f]\{3,4\}' ${cssFileName} | sort -u