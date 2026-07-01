import fs from 'fs';
import jsdom from 'jsdom';
const { JSDOM } = jsdom;

const html = fs.readFileSync('test_html.html', 'utf8');

const dom = new JSDOM(html, { runScripts: "dangerously" });
const window = dom.window;

window.onerror = function(message, source, lineno, colno, error) {
    console.log("JS Error:", message, "at line:", lineno);
};

setTimeout(() => {
    console.log("Chart is defined?", !!window.chartPesertaDidik);
    if (window.chartPesertaDidik) {
        console.log("Chart labels:", window.chartPesertaDidik.data.labels);
    }
}, 1000);
