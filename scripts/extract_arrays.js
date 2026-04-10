const fs = require('fs');
const data = fs.readFileSync('nist_bundle.js', 'utf8');

// The bundle seems to have parallel arrays like:
// idArray = ["GV.OC-01", "GV.OC-02", ...]
// titleArray = ["Organizational context understood", "Internal stakeholders informed", ...]
// We need to locate them.

// Let's find "GV.OC-01"
let idx1 = data.indexOf('"GV.OC-01"');
console.log("Index of GV.OC-01:", idx1);
if (idx1 > 0) {
    console.log(data.substring(idx1 - 50, idx1 + 200));
}

// Let's find "Organizational context understood" 
let idx2 = data.indexOf('"Organizational context understood"');
console.log("Index of Organizational context understood:", idx2);
if (idx2 > 0) {
    console.log(data.substring(idx2 - 50, idx2 + 200));
}

// Let's search for the first Panda explanation, "Identify the core business values" maybe?
let pandaIdx = data.toLowerCase().indexOf('panda');
console.log("First panda index:", pandaIdx);
if (pandaIdx > 0) {
    console.log(data.substring(pandaIdx - 50, pandaIdx + 200));
}
