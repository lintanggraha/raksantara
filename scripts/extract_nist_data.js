const https = require('https');
const fs = require('fs');

https.get('https://dashboards.infosecpanda.com/assets/index-Bw2DbJcG.js', (res) => {
    let rawData = '';
    res.on('data', (chunk) => { rawData += chunk; });
    res.on('end', () => {
        // Find arrays in the raw Javascript.
        // We are looking for an array that contains objects with "subcategoryId":"GV.OC-01" or similar.
        // The data is likely something like: const data = [{subcategoryId:"GV.OC-01", ...}, ...]
        
        // We'll write the whole bundle to disk temporarily, then use regex/eval to get it.
        fs.writeFileSync('nist_bundle.js', rawData);
        console.log("Bundle saved to nist_bundle.js");
        
        // Find the index of GV.OC-01
        const gvIndex = rawData.indexOf('GV.OC-01');
        if (gvIndex > -1) {
             // grab 500 chars before and after to analyze
             console.log(rawData.substring(gvIndex - 100, gvIndex + 400));
        }
    });
});
