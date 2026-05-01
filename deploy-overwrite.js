'use strict';
const https = require('https');
const fs = require('fs');
const BASE = 'C:\\Users\\Christian Hesselmann\\Documents\\Claude\\Projects\\wp-plugins\\';
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
  log('\n=== ThemeFlex v4.0.0 OVERWRITE ' + new Date().toISOString() + ' ===');

  // Step 1: GET login page for cookie
  log('[1] Getting login page...');
  await req('GET', '/wp-login.php', {});

  // Step 2: Login
  log('[2] Logging in...');
  let loggedIn = false;
  for (const pwd of PWDs) {
    const body = `log=${encodeURIComponent(USR)}&pwd=${encodeURIComponent(pwd)}&wp-submit=Log+In&redirect_to=%2Fwp-admin%2F&testcookie=1`;
    const r = await req('POST', '/wp-login.php', { 'Content-Type': 'application/x-www-form-urlencoded', 'Content-Length': Buffer.byteLength(body) }, body);
    log('  Login with', pwd.substring(0,4)+'*** status:', r.status, 'loc:', r.loc.substring(0,40));
    if (cookies.includes('wordpress_logged_in')) { loggedIn = true; break; }
  }
  if (!loggedIn) { log('ERROR: Login failed'); process.exit(1); }
  log('  Logged in OK');

  // Step 3: Follow overwrite link
  log('[3] Confirming overwrite (3.2.0 → 4.0.0)...');
  const overwriteUrl = '/wp-admin/update.php?action=upload-theme&package=119&overwrite=update-theme&_wpnonce=2ca792f320';
  const r = await req('GET', overwriteUrl, {});
  log('  Overwrite status:', r.status);
  fs.writeFileSync(BASE + 'overwrite-response.html', r.body);
  log('  Response saved to overwrite-response.html (' + r.body.length + ' chars)');

  if (r.body.includes('Theme updated successfully') || r.body.includes('updated successfully') || r.body.includes('Successfully updated')) {
    log('\nSUCCESS: ThemeFlex v4.0.0 LIVE on themeflex.de!');
  } else if (r.body.includes('Activate') || r.body.includes('activate')) {
    log('\nSUCCESS: Theme uploaded — check overwrite-response.html');
  } else {
    log('\nWARNING: Unexpected response — check overwrite-response.html');
  }
  log('Done:', new Date().toISOString());
}

main().catch(e => { fs.appendFileSync(LOG, 'FATAL: ' + e.stack + '\n'); process.exit(1); });
