const fs = require('fs');
const out = 'C:\\Users\\Christian Hesselmann\\Documents\\Claude\\Projects\\wp-plugins\\test-output.txt';
fs.writeFileSync(out, 'Node works! cwd=' + process.cwd() + '\nZIP=' + fs.existsSync('C:\\Users\\Christian Hesselmann\\Documents\\Claude\\Projects\\wp-plugins\\themeflex-v4.0.0.zip') + '\n');
