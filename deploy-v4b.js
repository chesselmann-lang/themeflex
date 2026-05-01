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

async function main() {
  log('\n=== ThemeFlex v4.0.0 DEPLOY+REPLACE ' + new Date().toISOString() + ' ===');

  // 1: Login page
  log('[1] Getting login page...');
  await req('GET', '/wp-login.php', {});

  // 2: Login
  log('[2] Logging in...');
  let loggedIn = false;
  for (const pwd of PWDs) {
    const body = `log=${encodeURIComponent(USR)}&pwd=${encodeURIComponent(pwd)}&wp-submit=Log+In&redirect_to=%2Fwp-admin%2F&testcookie=1`;
    const r = await req('POST', '/wp-login.php', { 'Content-Type': 'application/x-www-form-urlencoded', 'Content-Length': Buffer.byteLength(body) }, body);
    log('  Login', pwd.substring(0,4)+'*** status:', r.status);
    if (cookies.includes('wordpress_logged_in')) { loggedIn = true; break; }
  }
  if (!loggedIn) { log('ERROR: Login failed'); process.exit(1); }

  // 3: Nonce
  log('[3] Fetching upload nonce...');
  const np = await req('GET', '/wp-admin/theme-install.php', {});
  const nm = np.body.match(/name="_wpnonce"\s+value="([^"]+)"/);
  if (!nm) { log('ERROR: No nonce found'); process.exit(1); }
  const nonce = nm[1];
  log('  Nonce:', nonce);

  // 4: Build multipart + upload
  log('[4] Building multipart upload...');
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
  log('  ZIP size:', Math.round(zipBytes.length/1024/1024*10)/10, 'MB');

  log('[5] Uploading...');
  const up = await req('POST', '/wp-admin/update.php?action=upload-theme', {
    'Content-Type': `multipart/form-data; boundary=${boundary}`,
    'Content-Length': bodyBuf.length,
    'Referer': `https://${WP}/wp-admin/theme-install.php`
  }, bodyBuf);
  log('  Upload status:', up.status);
  fs.writeFileSync(BASE + 'deploy-response.html', up.body);

  // 5: Parse response — check for replace link or success
  if (up.body.includes('Theme updated successfully') || up.body.includes('updated successfully')) {
    log('\nSUCCESS: ThemeFlex v4.0.0 is LIVE!');
    log('Done:', new Date().toISOString());
    return;
  }

  // Look for "Replace installed with uploaded" href
  const replaceMatch = up.body.match(/href="(update\.php\?action=upload-theme[^"]*overwrite=update-theme[^"]*)"/);
  if (replaceMatch) {
    let replaceUrl = '/wp-admin/' + replaceMatch[1].replace(/&amp;/g, '&');
    log('[6] Found replace link — following:', replaceUrl.substring(0, 80));
    const rr = await req('GET', replaceUrl, { 'Referer': `https://${WP}/wp-admin/update.php?action=upload-theme` });
    log('  Replace status:', rr.status);
    fs.writeFileSync(BASE + 'replace-response.html', rr.body);
    log('  Response saved to replace-response.html (' + rr.body.length + ' chars)');
    if (rr.body.includes('updated successfully') || rr.body.includes('Theme updated') || rr.body.includes('Activate') || rr.status === 200) {
      log('\nSUCCESS: ThemeFlex v4.0.0 REPLACED and is LIVE on themeflex.de!');
    } else {
      log('\nWARNING: Check replace-response.html — status ' + rr.status);
    }
  } else {
    log('WARNING: No replace link found — check deploy-response.html');
    log('Body excerpt:', up.body.substring(0, 500));
  }
  log('Done:', new Date().toISOString());
}

main().catch(e => { fs.appendFileSync(LOG, 'FATAL: ' + e.stack + '\n'); process.exit(1); });
