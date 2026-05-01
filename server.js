const http = require('http');
const fs = require('fs');
const path = require('path');
const dir = 'C:\\Users\\Christian Hesselmann\\Documents\\Claude\\Projects\\wp-plugins';
http.createServer((req, res) => {
  const fp = path.join(dir, req.url === '/' ? 'index.html' : req.url);
  fs.readFile(fp, (e, d) => {
    if (e) { res.writeHead(404); res.end('Not found'); }
    else { res.writeHead(200, {'Content-Type': 'text/html; charset=utf-8'}); res.end(d); }
  });
}).listen(8080, () => console.log('OK'));
