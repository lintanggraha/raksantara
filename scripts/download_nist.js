const https = require('https');
const fs = require('fs');

const url = "https://csrc.nist.gov/CSRC/media/Publications/sp/1299/final/documents/nist-csf-2.0-core-with-sp800-53-mappings.csv";

https.get(url, (res) => {
    let rawData = '';
    
    // Check if the URL redirects or returns 200
    if (res.statusCode !== 200 && res.statusCode !== 301 && res.statusCode !== 302) {
        console.error(`Request Failed. Status Code: ${res.statusCode}`);
        res.resume();
        return;
    }

    if (res.statusCode === 301 || res.statusCode === 302) {
        console.log("Redirected to:", res.headers.location);
        return;
    }

    res.setEncoding('utf8');
    res.on('data', (chunk) => { rawData += chunk; });
    res.on('end', () => {
        try {
            console.log("Downloaded CSV length:", rawData.length);
            fs.writeFileSync('nist_official.csv', rawData);
            // Print first 500 chars
            console.log(rawData.substring(0, 500));
        } catch (e) {
            console.error(e.message);
        }
    });
}).on('error', (e) => {
    console.error(`Got error: ${e.message}`);
});
