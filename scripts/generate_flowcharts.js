const fs = require('fs');
const zlib = require('zlib');
const https = require('https');
const path = require('path');

const mdPath = path.join(__dirname, '../docs/flowchart_dan_alur_sistem.md');
const imgDir = path.join(__dirname, '../docs/images');

if (!fs.existsSync(imgDir)) {
    fs.mkdirSync(imgDir, { recursive: true });
}

let mdContent = fs.readFileSync(mdPath, 'utf8');
const mermaidRegex = /```mermaid\n([\s\S]*?)\n```/g;
let matches = [];
let match;
while ((match = mermaidRegex.exec(mdContent)) !== null) {
    matches.push({ full: match[0], code: match[1].trim() });
}

async function downloadDiagram(text, index) {
    const data = Buffer.from(text, 'utf8');
    const compressed = zlib.deflateSync(data);
    const payload = compressed.toString('base64').replace(/\+/g, '-').replace(/\//g, '_');
    
    // We will download as PNG to make it a standard image file with hi-res
    const url = `https://kroki.io/mermaid/svg/${payload}`;
    const filename = `flowchart_${index}.svg`;
    const dest = path.join(imgDir, filename);
    
    return new Promise((resolve, reject) => {
        https.get(url, (res) => {
            if (res.statusCode !== 200) {
                reject(new Error(`Kroki returned ${res.statusCode} for flowchart ${index}`));
                return;
            }
            const file = fs.createWriteStream(dest);
            res.pipe(file);
            file.on('finish', () => resolve(`![Flowchart ${index}](./images/${filename})`));
        }).on('error', reject);
    });
}

// Map the index to the section names for better filename & alt text
const sections = [
    "Identifikasi & Registrasi",
    "Manajemen KRI",
    "Mitigation Kanban",
    "Reporting & Dashboard",
    "BCM & Insiden",
    "Kepatuhan & Audit",
    "Konfigurasi Superadmin"
];

async function run() {
    let newContent = mdContent;
    for (let i = 0; i < matches.length; i++) {
        console.log(`Processing flowchart ${i + 1}...`);
        try {
            const data = Buffer.from(matches[i].code, 'utf8');
            const compressed = zlib.deflateSync(data);
            const payload = compressed.toString('base64').replace(/\+/g, '-').replace(/\//g, '_');
            
            const filename = `flowchart_${i + 1}.png`; // Changed to PNG for maximum compatibility
            const url = `https://kroki.io/mermaid/png/${payload}`;
            const dest = path.join(imgDir, filename);
            
            await new Promise((resolve, reject) => {
                https.get(url, (res) => {
                    if (res.statusCode !== 200) {
                        reject(new Error(`Kroki returned ${res.statusCode} for flowchart ${i+1}`));
                        return;
                    }
                    const file = fs.createWriteStream(dest);
                    res.pipe(file);
                    file.on('finish', resolve);
                }).on('error', reject);
            });
            
            const altName = sections[i] ? sections[i] : `Flowchart ${i+1}`;
            const imgTag = `![Flowchart ${altName}](./images/${filename})`;
            newContent = newContent.replace(matches[i].full, imgTag);
            console.log(`Flowchart ${i + 1} saved successfully as PNG.`);
        } catch (e) {
            console.error(e);
        }
    }
    fs.writeFileSync(mdPath, newContent);
    console.log('All flowcharts converted to real professional PNG images!');
}

run();
