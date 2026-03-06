const XLSX = require('xlsx');
const wb = XLSX.readFile('c:\\Users\\HP\\Downloads\\products-export-2026-02-28.xlsx');
const ws = wb.Sheets[wb.SheetNames[0]];
const data = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' });
console.log('Sheet:', wb.SheetNames[0]);
console.log('Total rows:', data.length);
console.log('\n=== HEADERS ===');
data[0].forEach((h, i) => console.log(`  Col${i+1}: [${h}]`));
for (let r = 1; r < Math.min(data.length, 6); r++) {
    console.log(`\n=== ROW ${r+1} ===`);
    data[0].forEach((h, i) => console.log(`  ${h}: [${data[r][i]}]`));
}
