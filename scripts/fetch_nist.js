const https = require('https');
const fs = require('fs');

https.get('https://dashboards.infosecpanda.com/assets/index-Bw2DbJcG.js', (res) => {
    let rawData = '';
    res.on('data', (chunk) => { rawData += chunk; });
    res.on('end', () => {
        // Simple regex to find arrays of objects that look like the data
        // For NIST we know there are 6 functions, 34 categories, 185 subcategories
        // We'll search for 'GV.OC-01' to find the subcategories array
        
        const gvOc01Index = rawData.indexOf('"GV.OC-01"');
        if (gvOc01Index !== -1) {
            console.log("Found GV.OC-01!");
        } else {
            console.log("GV.OC-01 not found.");
            fs.writeFileSync('bundle_dump.js', rawData);
        }
    });
});
