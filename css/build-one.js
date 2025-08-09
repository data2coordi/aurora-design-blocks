const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const file = process.argv[2];
if (!file) {
    console.error('No file provided');
    process.exit(1);
}

const filename = path.basename(file, '.css');
const output = `build/${filename}.css`;

fs.mkdirSync('build', { recursive: true });
execSync(`postcss "${file}" -o "${output}"`);
console.log(`Built: ${output}`);