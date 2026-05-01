#!/usr/bin/env node
/**
 * ThemeFlex v3.2.0 — Deploy Script
 * ════════════════════════════════════════════════════════════════
 * Deploys the full 114-template ThemeFlex theme to a live WP site
 * via the WordPress Theme Editor REST endpoint (admin-ajax.php).
 *
 * USAGE:
 *   node tf-deploy-v12.js \
 *     --wp-url  https://themeflex.de \
 *     --user    admin \
 *     --pass    yourAppPassword \
 *     --theme   themeflex \
 *     --src     /path/to/themeflex/
 *
 * REQUIREMENTS:
 *   • Node.js 18+ (built-in fetch)  OR  Node.js 14+ with node-fetch
 *   • WordPress Application Password (Users > Edit > Application Passwords)
 *   • Theme Editor must NOT be disabled (remove define('DISALLOW_FILE_EDIT', true) temporarily)
 *
 * WHAT IT DEPLOYS (v3.2.0 delta from v3.1.0):
 *   Core docs : README.txt, item-description.html
 *   Updated   : page-contact.php, page-portfolio.php, page-pricing.php,
 *               page-saas.php, page-sports.php
 *   New (90+) : All specialist page templates (acupuncturist, celebrant,
 *               chiropractor, cleaning-service, coffeeshop, counsellor,
 *               dietitian, dog-groomer, dog-trainer, driving-instructor,
 *               electrician, estate-agent, event-planner, financial-adviser,
 *               financial-advisor, florist, gardener, garage, hairdresser,
 *               hypnotherapist, jeweller, life-coach, locksmith, makeup-artist,
 *               massage-therapist, mortgage-broker, music-teacher, nursery,
 *               nutritional-therapist, osteopath, personal-chef,
 *               personal-stylist, personal-trainer, physio, physiotherapist,
 *               plumber, podiatrist, private-tutor, speech-therapist,
 *               surveyor, tailor, tattoo-artist, tutor, vet, veterinarian,
 *               veterinary, web-designer, wine-merchant, winery,
 *               yoga-instructor, + more)
 *
 * ════════════════════════════════════════════════════════════════
 */

'use strict';

const fs   = require('fs');
const path = require('path');

// ── CLI argument parsing ─────────────────────────────────────────
const args = {};
for (let i = 2; i < process.argv.length; i += 2) {
  const key = process.argv[i].replace(/^--/, '');
  args[key]  = process.argv[i + 1] || '';
}

const WP_URL   = (args['wp-url']  || '').replace(/\/$/, '');
const WP_USER  = args['user']  || '';
const WP_PASS  = args['pass']  || '';
const THEME    = args['theme'] || 'themeflex';
const SRC_DIR  = args['src']   || path.join(__dirname, 'themeflex');

// ── Validate ─────────────────────────────────────────────────────
if (!WP_URL || !WP_USER || !WP_PASS) {
  console.error([
    '',
    '  ThemeFlex v3.2.0 Deploy',
    '  ─────────────────────────────────────────────────────────────',
    '  USAGE:',
    '    node tf-deploy-v12.js \\',
    '      --wp-url  https://themeflex.de \\',
    '      --user    admin \\',
    '      --pass    your-app-password \\',
    '      --theme   themeflex \\',
    '      --src     /path/to/local/themeflex/',
    '',
    '  ERROR: --wp-url, --user, and --pass are required.',
    '',
  ].join('\n'));
  process.exit(1);
}

if (!fs.existsSync(SRC_DIR)) {
  console.error(`\n  ERROR: Source directory not found: ${SRC_DIR}\n`);
  process.exit(1);
}

// ── Auth header ──────────────────────────────────────────────────
const AUTH_HEADER = 'Basic ' + Buffer.from(`${WP_USER}:${WP_PASS}`).toString('base64');

// ── Sleep helper ─────────────────────────────────────────────────
const sleep = ms => new Promise(r => setTimeout(r, ms));

// ── Colours ──────────────────────────────────────────────────────
const c = {
  reset  : '\x1b[0m',
  bold   : '\x1b[1m',
  green  : '\x1b[32m',
  red    : '\x1b[31m',
  yellow : '\x1b[33m',
  cyan   : '\x1b[36m',
  dim    : '\x1b[2m',
};
const ok   = s => `${c.green}✓${c.reset} ${s}`;
const fail = s => `${c.red}✗${c.reset} ${s}`;
const info = s => `${c.cyan}→${c.reset} ${s}`;
const dim  = s => `${c.dim}${s}${c.reset}`;

// ── Step 1: Fetch nonce ──────────────────────────────────────────
async function fetchNonce() {
  const url = `${WP_URL}/wp-admin/theme-editor.php?file=README.txt&theme=${THEME}`;
  console.log(info(`Fetching editor nonce from ${url}`));
  const res = await fetch(url, {
    headers: {
      Authorization: AUTH_HEADER,
      Cookie: '',
    },
    credentials: 'include',
    redirect: 'follow',
  });
  if (!res.ok) throw new Error(`HTTP ${res.status} fetching theme editor`);
  const html = await res.text();
  const m = html.match(/id="_wpnonce"[^>]*value="([^"]+)"/);
  if (!m) throw new Error('Could not extract _wpnonce. Check credentials and theme-editor access.');
  return m[1];
}

// ── Step 2: Push one file ────────────────────────────────────────
async function pushFile(nonce, relPath, content) {
  const fd = new FormData();
  fd.append('action',   'edit-theme-plugin-file');
  fd.append('nonce',    nonce);
  fd.append('file',     relPath);
  fd.append('theme',    THEME);
  fd.append('newcontent', content);

  const res = await fetch(`${WP_URL}/wp-admin/admin-ajax.php`, {
    method: 'POST',
    headers: { Authorization: AUTH_HEADER },
    body: fd,
  });
  if (!res.ok) throw new Error(`HTTP ${res.status} pushing ${relPath}`);
  const data = await res.json().catch(() => ({}));
  if (data.success === false) {
    throw new Error(`WP rejected ${relPath}: ${JSON.stringify(data.data || data)}`);
  }
  return data;
}

// ── Step 3: Collect files to deploy ─────────────────────────────
function collectFiles() {
  // v3.2.0 delta: updated + new files relative to SRC_DIR
  const delta = [
    // Core documentation
    'README.txt',
    'item-description.html',

    // Rewritten / expanded templates
    'page-contact.php',
    'page-portfolio.php',
    'page-pricing.php',
    'page-saas.php',
    'page-sports.php',

    // New specialist templates (all page-*.php not in v3.1.0)
    'page-acupuncturist.php',
    'page-automotive.php',
    'page-bakery.php',
    'page-beauty.php',
    'page-brewery.php',
    'page-caterer.php',
    'page-celebrant.php',
    'page-childcare.php',
    'page-childminder.php',
    'page-chiropractor.php',
    'page-cleaning-service.php',
    'page-clinic.php',
    'page-coffeeshop.php',
    'page-construction.php',
    'page-consultant.php',
    'page-counsellor.php',
    'page-coworking.php',
    'page-dental.php',
    'page-dentist.php',
    'page-dietitian.php',
    'page-dog-groomer.php',
    'page-dog-trainer.php',
    'page-driving-instructor.php',
    'page-ecommerce.php',
    'page-electrician.php',
    'page-estate-agent.php',
    'page-event-planner.php',
    'page-event.php',
    'page-financial-adviser.php',
    'page-financial-advisor.php',
    'page-fitness.php',
    'page-florist.php',
    'page-garage.php',
    'page-gardener.php',
    'page-gym-fitness.php',
    'page-gym.php',
    'page-hairdresser.php',
    'page-hypnotherapist.php',
    'page-interior-design.php',
    'page-interior-designer.php',
    'page-interior.php',
    'page-jeweller.php',
    'page-law.php',
    'page-lawfirm.php',
    'page-life-coach.php',
    'page-locksmith.php',
    'page-makeup-artist.php',
    'page-massage-therapist.php',
    'page-massage.php',
    'page-mortgage-broker.php',
    'page-music-teacher.php',
    'page-music.php',
    'page-nonprofit.php',
    'page-nursery.php',
    'page-nutritional-therapist.php',
    'page-nutritionist.php',
    'page-optician.php',
    'page-osteopath.php',
    'page-personal-chef.php',
    'page-personal-stylist.php',
    'page-personal-trainer.php',
    'page-pharmacy.php',
    'page-photographer.php',
    'page-photography.php',
    'page-physio.php',
    'page-physiotherapist.php',
    'page-plumber.php',
    'page-podcast.php',
    'page-podiatrist.php',
    'page-private-tutor.php',
    'page-realestate.php',
    'page-restaurant.php',
    'page-solicitor.php',
    'page-spa.php',
    'page-speech-therapist.php',
    'page-startup.php',
    'page-surveyor.php',
    'page-tailor.php',
    'page-tattoo-artist.php',
    'page-tattoo.php',
    'page-tech.php',
    'page-tutor.php',
    'page-vet.php',
    'page-veterinarian.php',
    'page-veterinary.php',
    'page-web-designer.php',
    'page-wine-merchant.php',
    'page-winery.php',
    'page-yoga-instructor.php',
    'page-yoga.php',
  ];

  const files = [];
  for (const rel of delta) {
    const abs = path.join(SRC_DIR, rel);
    if (!fs.existsSync(abs)) {
      console.warn(`  ${c.yellow}⚠${c.reset}  Skipping (not found): ${rel}`);
      continue;
    }
    const content = fs.readFileSync(abs, 'utf8');
    files.push({ rel, content, size: Buffer.byteLength(content, 'utf8') });
  }
  return files;
}

// ── Main ─────────────────────────────────────────────────────────
(async () => {
  const ver = '3.2.0';
  console.log(`\n${c.bold}${c.cyan}  ThemeFlex v${ver} — Production Deploy${c.reset}`);
  console.log(`${'─'.repeat(60)}`);
  console.log(dim(`  Target : ${WP_URL}`));
  console.log(dim(`  Theme  : ${THEME}`));
  console.log(dim(`  Source : ${SRC_DIR}`));
  console.log('');

  // ── Collect files ────────────────────────────────────────────
  const files = collectFiles();
  const totalKB = (files.reduce((s, f) => s + f.size, 0) / 1024).toFixed(1);
  console.log(info(`${files.length} files to deploy · ${totalKB} KB total`));
  console.log('');

  // ── Nonce ────────────────────────────────────────────────────
  let nonce;
  try {
    nonce = await fetchNonce();
    console.log(ok(`Nonce acquired: ${nonce}`));
  } catch (err) {
    console.error(fail(`Failed to fetch nonce: ${err.message}`));
    console.error(`\n  Ensure:\n  1. You are using a valid WP Application Password\n  2. The theme editor is enabled\n  3. The theme "${THEME}" is installed\n`);
    process.exit(1);
  }
  console.log('');

  // ── Deploy loop ──────────────────────────────────────────────
  let pushed = 0;
  let failed = 0;
  const failures = [];

  for (const { rel, content, size } of files) {
    const kb = (size / 1024).toFixed(1);
    process.stdout.write(`  ${c.dim}→${c.reset} ${rel.padEnd(45)} ${c.dim}${kb} KB${c.reset} `);
    try {
      await pushFile(nonce, rel, content);
      process.stdout.write(`${c.green}✓${c.reset}\n`);
      pushed++;
    } catch (err) {
      process.stdout.write(`${c.red}✗ ${err.message}${c.reset}\n`);
      failures.push({ rel, err: err.message });
      failed++;
    }
    // Throttle to avoid overwhelming the server
    await sleep(120);
  }

  // ── Summary ──────────────────────────────────────────────────
  console.log(`\n${'─'.repeat(60)}`);
  console.log(`${c.bold}  Deploy complete${c.reset}`);
  console.log(`  ${c.green}✓ ${pushed} files pushed successfully${c.reset}`);
  if (failed > 0) {
    console.log(`  ${c.red}✗ ${failed} files failed${c.reset}`);
    for (const f of failures) {
      console.log(`    ${c.red}•${c.reset} ${f.rel}: ${f.err}`);
    }
    console.log('');
    console.log(`  Tip: Re-run to retry failed files, or use the WP Admin`);
    console.log(`       Theme Editor to push them manually.`);
  } else {
    console.log(`\n  ${c.green}All files deployed. ThemeFlex v${ver} is live.${c.reset}`);
    console.log(`\n  ${c.cyan}Post-deploy checks:${c.reset}`);
    console.log(`  1. Visit ${WP_URL}/?p=1 and verify the theme loads`);
    console.log(`  2. Check a page using a new specialist template`);
    console.log(`  3. Run Lighthouse on ${WP_URL}/ — target ≥ 95`);
    console.log(`  4. Clear any page/object caches (WP Rocket, Varnish, CDN)`);
  }
  console.log('');

  // ── Alternative: zip upload reminder ─────────────────────────
  console.log(`${'─'.repeat(60)}`);
  console.log(`  ${c.yellow}Alternative: full zip upload${c.reset}`);
  console.log(`  For a clean full-theme install use WP Admin > Appearance > Themes:`);
  console.log(`  ${c.dim}Upload: themeflex-v${ver}.zip (3.4 MB)${c.reset}`);
  console.log('');
})();
