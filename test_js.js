const jsdom = require("jsdom");
const { JSDOM } = jsdom;
const fs = require('fs');
const html = fs.readFileSync('test_html.html', 'utf8');

const dom = new JSDOM(html, { runScripts: "dangerously" });
const window = dom.window;

window.onerror = function(message, source, lineno, colno, error) {
    console.error("JS Error:", message);
};
console.log("Loaded JS. Check for errors above.");
