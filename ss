[1mdiff --git a/public/build/assets/tampilan-Bkw1RHQF.js b/public/build/assets/tampilan-Bkw1RHQF.js[m
[1mdeleted file mode 100644[m
[1mindex 077cf98..0000000[m
[1m--- a/public/build/assets/tampilan-Bkw1RHQF.js[m
[1m+++ /dev/null[m
[36m@@ -1 +0,0 @@[m
[31m-(function(){const s=localStorage.getItem("earsip-theme")||"light";document.documentElement.setAttribute("data-theme-changing",""),document.documentElement.setAttribute("data-theme",s)})();document.addEventListener("DOMContentLoaded",()=>{const a=document.body,s=document.querySelector(".pc-sidebar"),l=document.querySelector(".pc-header"),n=document.querySelector(".pc-container"),r=document.getElementById("theme-switch")||document.getElementById("theme-toggle"),i=document.getElementById("mobile-collapse"),f=document.getElementById("sidebar-hide"),d="earsip-theme",m="earsip-sidebar";requestAnimationFrame(()=>{requestAnimationFrame(()=>{document.documentElement.removeAttribute("data-theme-changing")})});let o=document.querySelector(".sidebar-overlay");o||(o=document.createElement("div"),o.className="sidebar-overlay",document.body.appendChild(o));function h(e,{instant:t=!1}={}){t&&document.documentElement.setAttribute("data-theme-changing",""),document.documentElement.setAttribute("data-theme",e),a.classList.toggle("dark-mode",e==="dark"),localStorage.setItem(d,e),t&&requestAnimationFrame(()=>{requestAnimationFrame(()=>{document.documentElement.removeAttribute("data-theme-changing")})})}const L=localStorage.getItem(d)||"light";h(L),r==null||r.addEventListener("click",()=>{const t=(localStorage.getItem(d)||"light")==="dark"?"light":"dark";h(t)}),s&&f&&f.addEventListener("click",e=>{e.preventDefault();const t=a.classList.toggle("sidebar-collapsed");s.classList.toggle("collapsed",t),s.style.transform=t?"translateX(-100%)":"translateX(0)",l&&(l.style.left=t?"0":"260px"),n&&(n.style.marginLeft=t?"0":"260px"),localStorage.setItem(m,t?"collapsed":"open")});function v(){s==null||s.classList.toggle("show"),o==null||o.classList.toggle("active")}i==null||i.addEventListener("click",e=>{e.preventDefault(),v()}),o==null||o.addEventListener("click",()=>{s==null||s.classList.remove("show"),o==null||o.classList.remove("active")});function p(){if(!(!s||!l||!n))if(window.innerWidth<=992)a.classList.remove("sidebar-collapsed"),s.classList.remove("collapsed","show"),o==null||o.classList.remove("active"),s.style.removeProperty("transform"),l.style.left="0",n.style.marginLeft="0";else{s.classList.remove("show"),o==null||o.classList.remove("active");const e=localStorage.getItem(m)==="collapsed";s.style.transform=e?"translateX(-100%)":"translateX(0)",l.style.left=e?"0":"260px",n.style.marginLeft=e?"0":"260px"}}if(window.addEventListener("resize",p),s){s.style.visibility="hidden";const e=localStorage.getItem(m);if(window.innerWidth>992){const t=e==="collapsed";a.classList.toggle("sidebar-collapsed",t),s.classList.toggle("collapsed",t),s.style.transform=t?"translateX(-100%)":"translateX(0)",l&&(l.style.left=t?"0":"260px"),n&&(n.style.marginLeft=t?"0":"260px")}else l&&(l.style.left="0"),n&&(n.style.marginLeft="0");setTimeout(()=>{s.style.visibility="visible"},30)}p();const A=window.location.pathname;document.querySelectorAll(".pc-item").forEach(e=>{const t=e.querySelector(".pc-link"),c=t==null?void 0:t.getAttribute("href");c&&A.startsWith(c)?e.classList.add("active"):e.classList.remove("active")}),document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(e=>{new bootstrap.Tooltip(e)});function S(e){const t=e.querySelector("thead");if(!t)return;const c=t.querySelectorAll("tr:last-child th, tr:last-child td");if(!c.length)return;const b=Array.from(c).map(u=>u.textContent.trim().replace(/\s+/g," "));e.querySelectorAll("tbody tr").forEach(u=>{const g=u.querySelectorAll("td");g.length===1&&g[0].hasAttribute("colspan")||g.forEach((q,E)=>{b[E]&&q.setAttribute("data-label",b[E])})})}function y(e){(e||document).querySelectorAll(".table-responsive table").forEach(S)}y(document);const w=new MutationObserver(()=>{clearTimeout(window.__earsipTableLabelTimer),window.__earsipTableLabelTimer=setTimeout(()=>y(document),50)});document.querySelectorAll(".table-responsive").forEach(e=>{w.observe(e,{childList:!0,subtree:!0})})});[m
[1mdiff --git a/public/build/manifest.json b/public/build/manifest.json[m
[1mindex 59c923a..18a6cec 100644[m
[1m--- a/public/build/manifest.json[m
[1m+++ b/public/build/manifest.json[m
[36m@@ -48,7 +48,7 @@[m
     "isEntry": true[m
   },[m
   "resources/js/tampilan.js": {[m
[31m-    "file": "assets/tampilan-Bkw1RHQF.js",[m
[32m+[m[32m    "file": "assets/tampilan-C3N237cW.js",[m
     "name": "tampilan",[m
     "src": "resources/js/tampilan.js",[m
     "isEntry": true[m
[1mdiff --git a/public/css/layout.css b/public/css/layout.css[m
[1mindex b721aa0..bb9974c 100644[m
[1m--- a/public/css/layout.css[m
[1m+++ b/public/css/layout.css[m
[36m@@ -524,7 +524,7 @@[m [m@media (max-width: 992px) {[m
     box-shadow: 3px 0 15px rgba(0,0,0,0.4);[m
     transform: translateX(-100%);[m
     transition: transform 0.3s ease;[m
[31m-    z-index: 1090;[m
[32m+[m[32m    z-index: 1101;[m
   }[m
 [m
   .pc-sidebar.show {[m
[1mdiff --git a/resources/js/tampilan.js b/resources/js/tampilan.js[m
[1mindex dea431c..9add525 100644[m
[1m--- a/resources/js/tampilan.js[m
[1m+++ b/resources/js/tampilan.js[m
[36m@@ -10,239 +10,262 @@[m
 // sudah aktif sebelum konten ke-render → tidak ada "flick" putih.[m
 // =======================================================[m
 (function () {[m
[31m-  const THEME_KEY = "earsip-theme";[m
[31m-  const saved = localStorage.getItem(THEME_KEY) || "light";[m
[32m+[m[32m    const THEME_KEY = "earsip-theme";[m
[32m+[m[32m    const saved = localStorage.getItem(THEME_KEY) || "light";[m
 [m
[31m-  // Matikan semua transisi sesaat agar penerapan tema awal tidak "flick"[m
[31m-  document.documentElement.setAttribute("data-theme-changing", "");[m
[31m-  document.documentElement.setAttribute("data-theme", saved);[m
[32m+[m[32m    // Matikan semua transisi sesaat agar penerapan tema awal tidak "flick"[m
[32m+[m[32m    document.documentElement.setAttribute("data-theme-changing", "");[m
[32m+[m[32m    document.documentElement.setAttribute("data-theme", saved);[m
 })();[m
 [m
 document.addEventListener("DOMContentLoaded", () => {[m
[31m-  const body = document.body;[m
[31m-  const sidebar = document.querySelector(".pc-sidebar");[m
[31m-  const header = document.querySelector(".pc-header");[m
[31m-  const container = document.querySelector(".pc-container");[m
[31m-  const themeSwitch = document.getElementById("theme-switch") || document.getElementById("theme-toggle");[m
[31m-  const mobileToggle = document.getElementById("mobile-collapse");[m
[31m-  const sidebarHide = document.getElementById("sidebar-hide");[m
[31m-[m
[31m-  // ====== LocalStorage Keys ======[m
[31m-  const THEME_KEY = "earsip-theme";[m
[31m-  const SIDEBAR_KEY = "earsip-sidebar";[m
[31m-[m
[31m-  // Lepas flag anti-flicker setelah frame pertama selesai render,[m
[31m-  // supaya transisi normal (hover, toggle, dll) tetap smooth setelahnya.[m
[31m-  requestAnimationFrame(() => {[m
[32m+[m[32m    const body = document.body;[m
[32m+[m[32m    const sidebar = document.querySelector(".pc-sidebar");[m
[32m+[m[32m    const header = document.querySelector(".pc-header");[m
[32m+[m[32m    const container = document.querySelector(".pc-container");[m
[32m+[m[32m    const themeSwitch =[m
[32m+[m[32m        document.getElementById("theme-switch") ||[m
[32m+[m[32m        document.getElementById("theme-toggle");[m
[32m+[m[32m    const mobileToggle = document.getElementById("mobile-collapse");[m
[32m+[m[32m    const sidebarHide = document.getElementById("sidebar-hide");[m
[32m+[m
[32m+[m[32m    // ====== LocalStorage Keys ======[m
[32m+[m[32m    const THEME_KEY = "earsip-theme";[m
[32m+[m[32m    const SIDEBAR_KEY = "earsip-sidebar";[m
[32m+[m
[32m+[m[32m    // Lepas flag anti-flicker setelah frame pertama selesai render,[m
[32m+[m[32m    // supaya transisi normal (hover, toggl