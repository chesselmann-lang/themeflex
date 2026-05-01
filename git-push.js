'use strict';
const { execSync } = require('child_process');
const fs = require('fs');
const GIT  = 'C:\\Program Files\\Git\\cmd\\git.exe';
const BASE = 'C:\\Users\\Christian Hesselmann\\Documents\\Claude\\Projects\\wp-plugins';
const LOG  = BASE + '\\git-push.log';
const REMOTE = 'https://github.com/whatsdigital/themeflex.git';

const log = (...a) => { const line = a.join(' '); process.stdout.write(line+'\n'); fs.appendFileSync(LOG, line+'\n'); };
const run = (cmd, opts) => {
  try {
    const out = execSync(cmd, { cwd: BASE, encoding: 'utf8', stdio: 'pipe', ...opts });
    return { ok: true, out: (out||'').trim() };
  } catch(e) {
    return { ok: false, out: (e.stdout||'').trim(), err: (e.stderr||'').trim() };
  }
};

fs.writeFileSync(LOG, '=== ThemeFlex GitHub Push ' + new Date().toISOString() + ' ===\n');
log('Working dir:', BASE);

// Check git status
log('\n[1] Git status...');
let r = run(`"${GIT}" status`);
log(r.out || r.err);

// Check if already a git repo
const isRepo = r.out.includes('On branch') || r.out.includes('HEAD');
if (!isRepo && r.err.includes('not a git repository')) {
  log('\n[INIT] Not a git repo — initializing...');
  r = run(`"${GIT}" init`);
  log(r.ok ? 'Init: ' + r.out : 'Init error: ' + r.err);
}

// Check/set remote
log('\n[2] Checking remote...');
r = run(`"${GIT}" remote -v`);
log(r.out || r.err || '(no remotes)');

if (!r.out.includes('origin')) {
  log('  Adding remote origin...');
  r = run(`"${GIT}" remote add origin ${REMOTE}`);
  log(r.ok ? 'Remote added' : 'Error: ' + r.err);
}

// Set user config if needed
run(`"${GIT}" config user.email "christian@whatsdigital.de"`);
run(`"${GIT}" config user.name "WhatsDigital"`);

// Stage all
log('\n[3] Staging all files...');
r = run(`"${GIT}" add -A`);
log(r.ok ? 'Staged OK' : 'Error: ' + r.err);

// Check what's staged
r = run(`"${GIT}" diff --cached --stat`);
log(r.out ? r.out.substring(0, 500) : '(nothing staged)');

// Commit
log('\n[4] Committing...');
r = run(`"${GIT}" commit -m "feat: ThemeFlex v4.0.0 — AI-Chat, DSGVO-Wizard, 114 Premium Templates"`);
if (r.ok) {
  log('Commit: ' + r.out.substring(0, 200));
} else if (r.err.includes('nothing to commit') || r.out.includes('nothing to commit')) {
  log('Nothing new to commit — already up to date');
} else {
  log('Commit error: ' + r.err.substring(0, 300));
}

// Push
log('\n[5] Pushing to GitHub...');
r = run(`"${GIT}" push -u origin main --force`);
if (r.ok) {
  log('Push OK: ' + r.out.substring(0, 300));
} else {
  // Try master branch
  log('main failed: ' + r.err.substring(0, 200));
  log('  Trying push to master...');
  r = run(`"${GIT}" push -u origin master --force`);
  if (r.ok) {
    log('Push to master OK: ' + r.out.substring(0, 300));
  } else {
    log('Push error: ' + r.err.substring(0, 400));
    log('\n=== Manual action needed ===');
    log('Run: git push -u origin main --force');
    log('If auth fails, use GitHub token or SSH key');
  }
}

log('\nDone: ' + new Date().toISOString());
