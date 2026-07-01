const fs = require('fs');
const jsdom = require("jsdom");
const { JSDOM } = jsdom;
const html = fs.readFileSync('test_html.html', 'utf8');

const dom = new JSDOM(html, { runScripts: "dangerously" });
const window = dom.window;

window.onerror = function(message, source, lineno, colno, error) {
    console.log("JS Error:", message);
};

// Wait for a little bit for DOMContentLoaded to fire and charts to initialize
setTimeout(() => {
    console.log("Checking chartPesertaDidik...");
    if (window.chartPesertaDidik) {
        console.log("Chart is defined.");
        const generateLabels = window.chartPesertaDidik.options?.plugins?.legend?.labels?.generateLabels;
        console.log("generateLabels exists:", !!generateLabels);
        
        // Trigger updateChartsForTheme
        console.log("Triggering updateChartsForTheme...");
        if (typeof window.updateChartsForTheme === 'function') {
            try {
                window.updateChartsForTheme();
                console.log("updateChartsForTheme ran successfully.");
            } catch (e) {
                console.log("Error in updateChartsForTheme:", e.message);
            }
        } else {
            console.log("updateChartsForTheme is not a global function.");
        }
    } else {
        console.log("Chart not defined.");
    }
}, 1000);
