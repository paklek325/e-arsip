(function(){const r={filterFormId:"filter_form",bulanGroupId:"bulan_group",bulanSelectId:"bulan",tipeRekapId:"tipe_rekap",tahunInputId:"tahun",hasilContainerId:"laporan_hasil_container",printButtonId:"btn_print",resetButtonId:"btn_reset_filter",downloadGroupId:"download_group"};function o(t){return document.getElementById(t)}function y(t){t&&(t.style.display="block",t.innerHTML=`
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <p class="mt-2 text-muted">Memuat laporan...</p>
            </div>
        `)}function f(t,n){if(!t)return;t.style.display="block",t.innerHTML=`
            <div class="alert alert-danger p-4 shadow-sm">
                <h5><i class="fas fa-exclamation-triangle"></i> Gagal Memuat Laporan</h5>
                <p class="mb-0">${n}</p>
            </div>
        `;const e=o(r.printButtonId),a=o(r.downloadGroupId);e&&(e.style.display="none"),a&&(a.style.display="none")}function u(t){if(!t)return;t.style.display="block",t.innerHTML='<div class="text-muted text-center p-4">Silakan pilih filter untuk menampilkan laporan.</div>';const n=o(r.printButtonId),e=o(r.downloadGroupId);n&&(n.style.display="none"),e&&(e.style.display="none")}function w(t){const n=new FormData(t),e=new URLSearchParams;for(const[a,i]of n.entries())i&&String(i).trim()!==""&&e.append(a,i);return e.toString()}function I(t){const n=o(r.filterFormId);if(!n)return null;const e=n.getAttribute("data-export-url")||n.getAttribute("action");if(!e)return null;const a=new URL(window.location.href),i=new URLSearchParams(a.search);return i.set("format",t),`${e}?${i.toString()}`}function v({pushState:t=!0}={}){const n=o(r.filterFormId),e=o(r.tipeRekapId),a=o(r.hasilContainerId);if(!n||!a)return;if(!e||!e.value||e.value.trim()===""){u(a);const s=new URL(location.href);s.searchParams.delete("tipe"),s.searchParams.delete("tahun"),s.searchParams.delete("bulan"),s.searchParams.delete("jenis"),s.searchParams.delete("page"),t&&history.replaceState(null,"",s.toString());return}const i=n.getAttribute("action")||window.location.pathname,l=w(n),c=`${i}${l?"?"+l:""}`;p(c,{pushState:t})}async function p(t,{pushState:n=!0}={}){const e=o(r.hasilContainerId);if(e)try{y(e);const a=await fetch(t,{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest",Accept:"text/html"},credentials:"same-origin"});if(!a.ok){if(a.status===422){let s=await a.json().catch(()=>null),d="Kesalahan Validasi.";if(s&&s.errors){const k=Object.keys(s.errors)[0];d=s.errors[k]?s.errors[k][0]:d}f(e,d)}else{const s=await a.text().catch(()=>null);f(e,`Terjadi kesalahan server (${a.status}).`),console.error("Server error:",a.status,s)}return}const i=await a.text();e.innerHTML=i;const l=o(r.printButtonId),c=o(r.downloadGroupId);if(l&&(l.style.display="inline-block"),c&&(c.style.display="inline-block"),n)try{history.pushState({ajax:!0},"",t)}catch(s){console.warn("PushState blocked by browser:",s)}m()}catch(a){console.error("Fetch error:",a),f(e,"Tidak dapat terhubung ke server. Periksa koneksi Anda.")}}function h(){const t=o(r.tipeRekapId),n=o(r.bulanGroupId),e=o(r.bulanSelectId);!t||!n||(t.value==="Bulan"?n.style.display="block":(n.style.display="none",e&&(e.value="")))}function _(t){t.preventDefault();const n=o(r.tipeRekapId),e=o(r.tahunInputId),a=o(r.bulanSelectId),i=o(r.hasilContainerId);if(n&&(n.value=""),e){const c=e.getAttribute("data-default-year")||new Date().getFullYear();e.value=c}a&&(a.value=""),h(),i&&u(i);const l=new URL(location.href);l.searchParams.delete("tipe"),l.searchParams.delete("tahun"),l.searchParams.delete("bulan"),l.searchParams.delete("jenis"),l.searchParams.delete("page"),history.replaceState(null,"",l.toString())}function L(){const t=o(r.hasilContainerId);if(!t)return;const a=`<!doctype html><html><head>${Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).map(l=>l.outerHTML).join("")}
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
        <title>Cetak Laporan</title></head><body>${t.innerHTML}</body></html>`,i=window.open("","_blank");if(!i){alert("Pop-up diblokir. Izinkan pop-up untuk menggunakan fitur cetak.");return}i.document.open(),i.document.write(a),i.document.close(),i.focus(),setTimeout(()=>{i.print()},300)}function C(t){const n=t.target.closest(".download-link");if(!n)return;t.preventDefault();const e=n.getAttribute("data-format");if(!e)return;const a=I(e);if(!a){alert("Tidak dapat membuat URL download.");return}window.open(a,"_blank")}function S(t){let n=t.target.closest("a");if(!n)return;const e=n,a=e.classList.contains("ajax-link"),i=e.closest(".pagination");if(!a&&!i)return;t.preventDefault();const l=e.getAttribute("href");l&&p(l,{pushState:!0})}function m(){const t=o(r.hasilContainerId);t&&(t.__hasDelegated||(t.addEventListener("click",S),t.__hasDelegated=!0))}function b(t){const n=new URL(t),e=n.searchParams.get("tipe")||"",a=n.searchParams.get("tahun"),i=n.searchParams.get("bulan")||"",l=o(r.tipeRekapId),c=o(r.tahunInputId),s=o(r.bulanSelectId),d=c?c.getAttribute("data-default-year")||new Date().getFullYear():new Date().getFullYear();l&&(l.value=e),c&&(c.value=a||d),s&&(s.value=i),h()}function g(){const t=o(r.tipeRekapId),n=o(r.tahunInputId),e=o(r.bulanSelectId),a=o(r.printButtonId),i=o(r.resetButtonId),l=o(r.downloadGroupId),c=()=>{t&&h(),v()};t&&!t.__hasChange&&(t.addEventListener("change",c),t.__hasChange=!0),n&&!n.__hasChange&&(n.addEventListener("change",c),n.__hasChange=!0),e&&!e.__hasChange&&(e.addEventListener("change",c),e.__hasChange=!0),a&&!a.__hasClick&&(a.addEventListener("click",L),a.__hasClick=!0),i&&!i.__hasClick&&(i.addEventListener("click",_),i.__hasClick=!0),l&&!l.__hasClick&&(l.addEventListener("click",C),l.__hasClick=!0),m()}function x(t){b(location.href);const e=new URL(location.href).searchParams.get("tipe"),a=o(r.hasilContainerId);a&&(e?p(location.href,{pushState:!1}):u(a))}document.addEventListener("DOMContentLoaded",function(){g(),window.addEventListener("popstate",x);const t=o(r.hasilContainerId);if(t){t.style.display="none",t.innerHTML="";try{new URL(location.href).searchParams.get("tipe")?(b(location.href),p(location.href,{pushState:!1}).finally(()=>{t.style.display="block"})):u(t)}catch(n){console.error("Error during initial load check:",n),t.style.display="block"}}}),window.laporanReinit=function(){g()}})();
