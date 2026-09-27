import { readFileSync } from 'node:fs';
import { gzipSync } from 'node:zlib';

const manifest = JSON.parse(readFileSync('public/build/manifest.json', 'utf8'));

// Budgets are the 2026-09-27 output plus enough headroom (about 10–14%) to stay below the 90% warning.
// Built assets are measured gzipped; images and fonts are measured as stored files.
const budgets = [
    { entry: 'resources/css/app.css', label: 'Public stylesheet', maxGzipBytes: 24 * 1024 },
    { entry: 'resources/js/app.js', label: 'Public JavaScript', maxGzipBytes: 2.5 * 1024 },
    { entry: 'resources/css/filament/admin/theme.css', label: 'Filament admin theme', maxGzipBytes: 73 * 1024 },
    { entry: 'resources/js/filament/select-accessibility.js', label: 'Filament select accessibility', maxGzipBytes: 1024 },
    { file: 'public/images/hero-family-640.avif', label: 'Hero (640px AVIF)', maxBytes: 30 * 1024 },
    { file: 'public/images/hero-family-768.avif', label: 'Hero (768px AVIF)', maxBytes: 39 * 1024 },
    { file: 'public/images/hero-family-1024.avif', label: 'Hero (1024px AVIF)', maxBytes: 60 * 1024 },
    { file: 'public/images/hero-family-1600.avif', label: 'Hero (1600px AVIF)', maxBytes: 117 * 1024 },
    { file: 'public/images/hero-family-640.webp', label: 'Hero (640px WebP)', maxBytes: 76 * 1024 },
    { file: 'public/images/hero-family-768.webp', label: 'Hero (768px WebP)', maxBytes: 73 * 1024 },
    { file: 'public/images/hero-family-1024.webp', label: 'Hero (1024px WebP)', maxBytes: 139 * 1024 },
    { file: 'public/images/podcast/mouse28-cover-640.webp', label: 'Podcast cover (640px)', maxBytes: 52 * 1024 },
    { file: 'public/images/podcast/mouse28-cover-768.webp', label: 'Podcast cover (768px)', maxBytes: 60 * 1024 },
    { file: 'public/images/podcast/mouse28-cover.webp', label: 'Podcast cover (full WebP)', maxBytes: 112 * 1024 },
    { file: 'public/images/logo.webp', label: 'Logo', maxBytes: 82 * 1024 },
    { file: 'public/fonts/mouse28/besley-latin.woff2', label: 'Besley heading font', maxBytes: 40 * 1024 },
    ...[300, 400, 500, 600, 700].map(weight => ({
        file: `public/fonts/mouse28/poppins-${weight}.woff2`,
        label: `Poppins ${weight} body font`,
        maxBytes: 9 * 1024,
    })),
];

let failed = false;

for (const budget of budgets) {
    const asset = budget.entry ? manifest[budget.entry] : null;

    if (budget.entry && !asset) {
        throw new Error(`Vite manifest entry not found: ${budget.entry}`);
    }

    const path = budget.file ?? `public/build/${asset.file}`;
    const contents = readFileSync(path);
    const measuredBytes = budget.maxBytes === undefined ? gzipSync(contents, { level: 9 }).length : contents.length;
    const maxBytes = budget.maxBytes ?? budget.maxGzipBytes;
    const measurement = budget.maxBytes ? 'file' : 'gzip';

    console.log(
        `${budget.label}: ${(measuredBytes / 1024).toFixed(1)} KiB ${measurement} (limit ${(maxBytes / 1024).toFixed(1)} KiB)`,
    );

    if (measuredBytes >= maxBytes * 0.9 && measuredBytes <= maxBytes) {
        console.warn(`Asset budget warning: ${budget.label} is using at least 90% of its limit.`);
    }

    if (measuredBytes > maxBytes) {
        failed = true;
    }
}

if (failed) {
    console.error('Asset budget exceeded. Review the generated bundle before increasing the limit.');
    process.exitCode = 1;
}
