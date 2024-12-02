const fs = require('fs');
const path = require('path');

const buildDir = path.join(__dirname, '..', 'public', 'build', 'assets');
const target = path.join(__dirname, '..', 'public', 'assets', 'css', 'tailwind-app.css');

if (!fs.existsSync(buildDir)) {
    console.error('Build directory not found. Run vite build first.');
    process.exit(1);
}

const cssFile = fs.readdirSync(buildDir).find((file) => file.startsWith('app-') && file.endsWith('.css'));

if (!cssFile) {
    console.error('Built Tailwind CSS file not found.');
    process.exit(1);
}

fs.copyFileSync(path.join(buildDir, cssFile), target);
console.log(`Copied ${cssFile} -> public/assets/css/tailwind-app.css`);
