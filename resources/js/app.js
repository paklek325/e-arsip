import './bootstrap';
import flatpickr from "flatpickr";
import { Indonesian } from "flatpickr/dist/l10n/id.js";

flatpickr.localize(Indonesian);

import '../css/laporan.css';

// ── Global Toast Notification ──────────────────────────────────────────────
(function () {
    const CSS = `
        .app-toast{
            display:flex;align-items:flex-start;gap:12px;
            min-width:300px;max-width:380px;
            padding:14px 16px;margin-bottom:10px;
            border:0;border-radius:16px;color:#fff;
            box-shadow:0 12px 28px rgba(0,0,0,.18),0 2px 6px rgba(0,0,0,.08);
            position:relative;overflow:hidden;
            opacity:0;transform:translateX(40px) scale(.96);
            transition:opacity .45s cubic-bezier(.21,1.02,.73,1),
                       transform .45s cubic-bezier(.21,1.02,.73,1);
        }
        .app-toast.app-toast-in{opacity:1;transform:translateX(0) scale(1);}
        .app-toast.app-toast-out{opacity:0;transform:translateX(40px) scale(.96);}
        .app-toast-success{background:linear-gradient(135deg,#16a34a,#22c55e);}
        .app-toast-error{background:linear-gradient(135deg,#dc2626,#ef4444);}
        .app-toast-warning{background:linear-gradient(135deg,#d97706,#f59e0b);}
        .app-toast-info{background:linear-gradient(135deg,#2563eb,#3b82f6);}
        .app-toast-icon{
            flex-shrink:0;width:34px;height:34px;border-radius:50%;
            background:rgba(255,255,255,.22);
            display:flex;align-items:center;justify-content:center;
            font-size:17px;margin-top:1px;
        }
        .app-toast-body{flex:1;font-size:.9rem;font-weight:600;
            line-height:1.35;padding-top:4px;}
        .app-toast-close{
            flex-shrink:0;background:transparent;border:0;color:#fff;
            opacity:.85;font-size:1rem;line-height:1;padding:2px;
            margin-top:2px;cursor:pointer;
        }
        .app-toast-close:hover{opacity:1;}
        .app-toast-progress{
            position:absolute;left:0;bottom:0;height:3px;width:100%;
            background:rgba(255,255,255,.55);transform-origin:left;
            animation:appToastShrink 3.5s linear forwards;
        }
        @keyframes appToastShrink{from{transform:scaleX(1);}to{transform:scaleX(0);}}
    `;

    const style = document.createElement("style");
    style.id = "appToastStyles";
    style.textContent = CSS;
    document.head.appendChild(style);

    window.AppToast = function (message, type = "info") {
        let container = document.getElementById("customToastContainer");
        if (!container) {
            container = document.createElement("div");
            container.id = "customToastContainer";
            container.className = "position-fixed top-0 end-0 p-3 d-flex flex-column align-items-end";
            container.style.zIndex = "2000";
            document.body.appendChild(container);
        }

        const map = {
            success: ["app-toast-success", "bi-check-circle-fill"],
            error:   ["app-toast-error",   "bi-x-circle-fill"],
            warning: ["app-toast-warning", "bi-exclamation-triangle-fill"],
            info:    ["app-toast-info",    "bi-info-circle-fill"],
        };
        const [variant, icon] = map[type] ?? map.info;

        const el = document.createElement("div");
        el.className = `app-toast ${variant}`;
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        el.innerHTML = `
            <div class="app-toast-icon"><i class="bi ${icon}"></i></div>
            <div class="app-toast-body">${message}</div>
            <button type="button" class="app-toast-close" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="app-toast-progress"></div>
        `;
        container.appendChild(el);

        requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add("app-toast-in")));

        const remove = () => {
            el.classList.remove("app-toast-in");
            el.classList.add("app-toast-out");
            setTimeout(() => {
                el.remove();
                if (!container.children.length) container.remove();
            }, 450);
        };

        el.querySelector(".app-toast-close")?.addEventListener("click", remove);
        setTimeout(remove, 3500);
    };
})();
