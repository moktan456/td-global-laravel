#!/usr/bin/env node
/**
 * Makes resized WebP copies of every photo in public/photos (what next/image did on the fly in the Next.js site) and
 * writes resources/content/photo-widths.json, which the <x-photo> component reads to build a `srcset`.
 *
 *   npm run photos          # (re)build everything
 *
 * Originals stay untouched (they are the fallback `src`). Run it after adding or changing a photo, and commit the
 * result: the server never needs sharp.
 */
import { mkdir, readdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const root = path.resolve(import.meta.dirname, '..');
const srcDir = path.join(root, 'public', 'photos');
const outDir = path.join(srcDir, 'w');
const WIDTHS = [480, 768, 1080, 1440, 1600];

await mkdir(outDir, { recursive: true });
const manifest = {};
for (const file of (await readdir(srcDir)).filter((f) => /\.(jpe?g|png)$/i.test(f)).sort()) {
    const name = path.parse(file).name;
    const meta = await sharp(path.join(srcDir, file)).metadata();
    const widths = WIDTHS.filter((w) => w < meta.width);
    if (!widths.includes(meta.width) && meta.width <= Math.max(...WIDTHS)) widths.push(meta.width);
    for (const w of widths) {
        await sharp(path.join(srcDir, file)).rotate().resize({ width: w, withoutEnlargement: true }).webp({ quality: 76 }).toFile(path.join(outDir, `${name}-${w}.webp`));
    }
    manifest[name] = { width: meta.width, height: meta.height, widths };
}
await writeFile(path.join(root, 'resources', 'content', 'photo-widths.json'), JSON.stringify(manifest, null, 2) + '\n');
console.log(`Wrote ${Object.keys(manifest).length} photos × up to ${WIDTHS.length} sizes to public/photos/w and photo-widths.json`);
