(function(){const o={filterFormId:"filter_form",bulanGroupId:"bulan_group",bulanSelectId:"bulan",tipeRekapId:"tipe_rekap",tahunInputId:"tahun",hasilContainerId:"laporan_hasil_container",printButtonId:"btn_print",resetButtonId:"btn_reset_filter",downloadGroupId:"download_group"};function i(t){return document.getElementById(t)}function y(t){t&&(t.style.display="block",t.innerHTML=`
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <p class="mt-2 text-muted">Memuat laporan...</p>
            </div>
        `)}function f(t,e){if(!t)return;t.style.display="block",t.innerHTML=`
            <div class="alert alert-danger p-4 shadow-sm">
                <h5><i class="fas fa-exclamation-triangle"></i> Gagal Memuat Laporan</h5>
                <p class="mb-0">${e}</p>
            </div>
        `;const n=i(o.printButtonId),a=i(o.downloadGroupId);n&&(n.style.display="none"),a&&(a.style.display="none")}function d(t){if(!t)return;t.style.display="block",t.innerHTML='<div class="text-muted text-center p-4">Silakan pilih filter untuk menampilkan laporan.</div>';const e=i(o.printButtonId),n=i(o.downloadGroupId);e&&(e.style.display="none"),n&&(n.style.display="none")}function w(t){const e=new FormData(t),n=new URLSearchParams;for(const[a,r]of e.entries())r&&String(r).trim()!==""&&n.append(a,r);return n.toString()}function I(t){const e=i(o.filterFormId);if(!e)return null;const n=e.getAttribute("data-export-url")||e.getAttribute("action");if(!n)return null;const a=new URL(window.location.href),r=new URLSearchParams(a.search);return r.set("format",t),`${n}?${r.toString()}`}function v({pushState:t=!0}={}){const e=i(o.filterFormId);i(o.tipeRekapId);const n=i(o.hasilContainerId);if(!e||!n)return;p();const a=i(o.tahunInputId);if(!a||!a.value||a.value.trim()===""){d(n);const s=new URL(location.href);s.searchParams.delete("tipe"),s.searchParams.delete("tahun"),s.searchParams.delete("bulan"),s.searchParams.delete("jenis"),s.searchParams.delete("page"),t&&history.replaceState(null,"",s.toString());return}const r=e.getAttribute("action")||window.location.pathname,l=w(e),c=`${r}${l?"?"+l:""}`;u(c,{pushState:t})}async function u(t,{pushState:e=!0}={}){const n=i(o.hasilContainerId);if(n)try{y(n);const a=await fetch(t,{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest",Accept:"text/html"},credentials:"same-origin"});if(!a.ok){if(a.status===422){let s=await a.json().catch(()=>null),h="Kesalahan Validasi.";if(s&&s.errors){const g=Object.keys(s.errors)[0];h=s.errors[g]?s.errors[g][0]:h}f(n,h)}else{const s=await a.text().catch(()=>null);f(n,`Terjadi kesalahan server (${a.status}).`),console.error("Server error:",a.status,s)}return}const r=await a.text();n.innerHTML=r;const l=i(o.printButtonId),c=i(o.downloadGroupId);if(l&&(l.style.display="inline-block"),c&&(c.style.display="inline-block"),e)try{history.pushState({ajax:!0},"",t)}catch(s){console.warn("PushState blocked by browser:",s)}m()}catch(a){console.error("Fetch error:",a),f(n,"Tidak dapat terhubung ke server. Periksa koneksi Anda.")}}function p(){const t=i(o.bulanSelectId),e=i(o.tipeRekapId);e&&(e.value=t&&t.value?"Bulan":"Tahun")}function L(t){t.preventDefault(),i(o.tipeRekapId);const e=i(o.tahunInputId),n=i(o.bulanSelectId),a=i(o.hasilContainerId);if(e){const l=e.getAttribute("data-default-year")||new Date().getFullYear();e.value=l}n&&(n.value=""),p(),a&&d(a);const r=new URL(location.href);r.searchParams.delete("tipe"),r.searchParams.delete("tahun"),r.searchParams.delete("bulan"),r.searchParams.delete("jenis"),r.searchParams.delete("page"),history.replaceState(null,"",r.toString())}function _(){const t=i(o.hasilContainerId);if(!t)return;const a=`<!doctype html><html><head>${Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).map(l=>l.outerHTML).join("")}
            <style>
                @page { 
                    margin: 1cm;
                }
                @media print {
                    .card-footer, .btn-view, .btn-edit, .btn-delete, .pagination, .d-flex .ajax-link { 
                        display: none !important; 
                    }

                    a[href]:after {
                        content: none !important;
                    }

                    a.btn-outline-secondary,
                    a[title*="Kembali"],
                    .ajax-link {
                        display: none !important;
                    }

                    a {
                        text-decoration: none !important;
                        color: #000 !important;
                    }

                    body { font-size: 10pt; }
                    .table-custom { font-size: 9pt; }
                    .table-responsive { overflow: visible !important; }

                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                    }
                    th, td { 
                        border: 1px solid #ccc; 
                        padding: 5px; 
                    }

                    thead { 
                        display: table-header-group;
                    }
                    tfoot {
                        display: table-footer-group;
                    }

                    tr, th, td {
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                    }

                    .table-primary, .table-dark { 
                        background-color: #f0f0f0 !important; 
                        -webkit-print-color-adjust: exact; 
                        color-adjust: exact; 
                        color: #000 !important; 
                    }

                    h4 {
                        page-break-after: avoid !important;
                    }
                    .card, .table { 
                        page-break-inside: avoid !important; 
                        page-break-before: auto;
                    }

                    h5 { 
                        page-break-after: avoid; 
                        page-break-before: auto;
                    }

                    .d-flex, .justify-content-between { 
                        display: block !important; 
                    } 
                }
            </style>
        <title>Cetak Laporan</title></head><body>${t.innerHTML}</body></html>`,r=window.open("","_blank");if(!r){window.AppToast("Pop-up diblokir. Izinkan pop-up untuk menggunakan fitur cetak.","warning");return}r.document.open(),r.document.write(a),r.document.close(),r.focus(),setTimeout(()=>{r.print()},300)}function S(t){const e=t.target.closest(".download-link");if(!e)return;t.preventDefault();const n=e.getAttribute("data-format");if(!n)return;const a=I(n);if(!a){window.AppToast("Tidak dapat membuat URL download.","error");return}window.open(a,"_blank")}function C(t){let e=t.target.closest("a");if(!e)return;const n=e,a=n.classList.contains("ajax-link"),r=n.closest(".pagination");if(!a&&!r)return;t.preventDefault();const l=n.getAttribute("href");l&&u(l,{pushState:!0})}function m(){const t=i(o.hasilContainerId);t&&(t.__hasDelegated||(t.addEventListener("click",C),t.__hasDelegated=!0))}function b(t){const e=new URL(t);e.searchParams.get("tipe");const n=e.searchParams.get("tahun"),a=e.searchParams.get("bulan")||"";i(o.tipeRekapId);const r=i(o.tahunInputId),l=i(o.bulanSelectId),c=r?r.getAttribute("data-default-year")||new Date().getFullYear():new Date().getFullYear();r&&(r.value=n||c),l&&(l.value=a),p()}function k(){i(o.tipeRekapId);const t=i(o.tahunInputId),e=i(o.bulanSelectId),n=i(o.printButtonId),a=i(o.resetButtonId),r=i(o.downloadGroupId),l=()=>{p(),v()};t&&!t.__hasChange&&(t.addEventListener("change",l),t.__hasChange=!0),e&&!e.__hasChange&&(e.addEventListener("change",l),e.__hasChange=!0),n&&!n.__hasClick&&(n.addEventListener("click",_),n.__hasClick=!0),a&&!a.__hasClick&&(a.addEventListener("click",L),a.__hasClick=!0),r&&!r.__hasClick&&(r.addEventListener("click",S),r.__hasClick=!0),m()}function x(t){b(location.href);const n=new URL(location.href).searchParams.get("tipe"),a=i(o.hasilContainerId);a&&(n?u(location.href,{pushState:!1}):d(a))}document.addEventListener("DOMContentLoaded",function(){k(),window.addEventListener("popstate",x);const t=i(o.hasilContainerId);if(t){t.style.display="none",t.innerHTML="";try{new URL(location.href).searchParams.get("tipe")?(b(location.href),u(location.href,{pushState:!1}).finally(()=>{t.style.display="block"})):d(t)}catch(e){console.error("Error during initial load check:",e),t.style.display="block"}}}),window.laporanReinit=function(){k()}})();
