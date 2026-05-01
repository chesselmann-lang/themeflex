'use strict';
const https = require('https');
const fs = require('fs');

const BASE = 'C:\\Users\\Christian Hesselmann\\Documents\\Claude\\Projects\\wp-plugins\\';
const ZIP  = BASE + 'themeflex-v4.0.0.zip';
const LOG  = BASE + 'deploy-v4.log';
const WP   = 'themeflex.de';
const USR  = 'admin';
const PWDs = ['ThemeFlex2026!', 'Los3032!'];

process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
let cookies = '';
const log = (...a) => { const line = a.join(' '); process.stdout.write(line+'\n'); fs.appendFileSync(LOG, line+'\n'); };

fs.writeFileSync(LOG, '=== ThemeFlex v4.0.0 WP Deploy ' + new Date().toISOString() + ' ===\n');
log('ZIP:', fs.existsSync(ZIP) ? 'FOUND (' + (fs.statSync(ZIP).size/1048576).toFixed(1) + ' MB)' : 'NOT FOUND!');
if (!fs.existsSync(ZIP)) process.exit(1);

function req(method, path2, headers, body) {
  return new Promise((resolve, reject) => {
    const opts = { method, hostname: WP, path: path2, headers: { ...headers, Cookie: cookies, 'User-Agent': 'Mozilla/5.0' }, rejectUnauthorized: false };
    const r = https.request(opts, res => {
      const sc = res.headers['set-cookie'];
      if (sc) sc.forEach(c => { const p = c.split(';')[0]; if (!cookies.includes(p.split('=')[0])) cookies += (cookies?'; ':'')+p; });
      const chunks = [];
      res.on('data', d => chunks.push(d));
      res.on('end', () => resolve({ status: res.statusCode, loc: res.headers.location||'', body: Buffer.concat(chunks).toString() }));
    });
    r.on('error', reject);
    if (body) r.write(typeof body==='string'?body:body);
    r.end();
  });
}

async function tryLogin(pwd) {
  const body = `log=${encodeURIComponent(USR)}&pwd=${encodeURIComponent(pwd)}&wp-submit=Log+In&redirect_to=%2Fwp-admin%2F&testcookie=1`;
  const r = await req('POST', '/wp-login.php', { 'Content-Type': 'application/x-www-form-urlencoded', 'Content-Length': Buffer.byteLength(body) }, body);
  log('  Login with', pwd.substring(0,4)+'*** status:', r.status, 'loc:', r.loc.substring(0,40));
  return cookies.includes('wordpress_logged_in');
}

async function main() {
  log('\n[1] Getting login page...');
  await req('GET', '/wp-login.php', {});

  log('[2] Logging in...');
  let ok = false;
  for (const pwd of PWDs) { ok = await tryLogin(pwd); if (ok) break; cookies=''; }
  if (!ok) { log('ERROR: Login failed with all passwords!'); process.exit(1); }
  log('Logged in OK. Cookies:', cookies.substring(0,60)+'...');

  log('\n[3] Fetching nonce...');
  const r3 = await req('GET', '/wp-admin/theme-install.php', {});
  log('  Nonce page status:', r3.status);
  const nm = r3.body.match(/"_ajax_nonce":"([^"]+)"/) || r3.body.match(/name="_wpnonce"\s+value="([^"]+)"/) || r3.body.match(/"themeupload":"([^"]+)"/);
  if (!nm) { log('ERROR: No nonce found. Page snippet:', r3.body.substring(0,300)); process.exit(1); }
  const nonce = nm[1];
  log('  Nonce:', nonce);

  log('\n[4] Building multipart upload...');
  const boundary = 'TFboundary' + Date.now();
  const zipBytes = fs.readFileSync(ZIP);
  const field = (name, val) => Buffer.from(`--${boundary}\r\nContent-Disposition: form-data; name="${name}"\r\n\r\n${val}\r\n`);
  const bodyBuf = Buffer.concat([
    field('_wpnonce', nonce),
    field('_wp_http_referer', '/wp-admin/theme-install.php'),
    field('install-theme-submit', 'Install Now'),
    Buffer.from(`--${boundary}\r\nContent-Disposition: form-data; name="themezip"; filename="themeflex-v4.0.0.zip"\r\nContent-Type: application/zip\r\n\r\n`),
    zipBytes,
    Buffer.from(`\r\n--${boundary}--\r\n`)
  ]);
  log('  Body size:', (bodyBuf.length/1048576).toFixed(1), 'MB');

  log('[5] Uploading to WP Admin...');
  const r4 = await req('POST', '/wp-admin/update.php?action=upload-theme', {
    'Content-Type': `multipart/form-data; boundary=${boundary}`,
    'Content-Length': bodyBuf.length,
    'Referer': 'https://themeflex.de/wp-admin/theme-install.php'
  }, bodyBuf);
  log('  Upload status:', r4.status);
  const html = r4.body;
  fs.writeFileSync(BASE + 'deploy-response.html', html);
  log('  Response saved to deploy-response.html (' + html.length + ' chars)');

  if (/Theme installiert|Successfully installed|Aktivieren|activate/i.test(html)) {
    log('\nSUCCESS: ThemeFlex v4.0.0 installed on themeflex.de!');
  } else if (/already exists|bereits vorhanden|Replace current/i.test(html)) {
    log('Theme exists — looking for replace/overwrite link...');
    const rm = html.match(/href="([^"]*(?:replace|overwrite|Replace)[^"]*)"/i);
    if (rm) {
      let ru = rm[1].replace(/&amp;/g,'&');
      if (!ru.startsWith('http')) ru = 'https://' + WP + ru;
      log('Replace URL:', ru.substring(0,100));
      const u = new URL(ru);
      const r5 = await req('GET', u.pathname + u.search, { 'Referer': 'https://themeflex.de/wp-admin/' });
      log('Replace status:', r5.status);
      fs.writeFileSync(BASE + 'deploy-replace.html', r5.body);
      if (/installiert|installed|updated|aktualisiert/i.test(r5.body)) { log('SUCCESS: Theme replaced!'); }
      else { log('Check deploy-replace.html for details.'); }
    } else { log('No replace link found — check deploy-response.html'); }
  } else {
    log('UNEXPECTED response — check deploy-response.html');
  }
  log('\nDone:', new Date().toISOString());
}

main().catch(e => { fs.appendFileSync(LOG, 'FATAL: ' + e.stack + '\n'); process.exit(1); });
