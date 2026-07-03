window.SuratApp?console.warn("[surat.js] SuratApp sudah terdaftar, skip re-init."):(()=>{var X;if(!document.querySelector("#page-surat"))return;const A=document.querySelector("#tableContainer");if(!A)return;const z=((X=document.querySelector('meta[name="csrf-token"]'))==null?void 0:X.getAttribute("content"))||"",o=t=>document.querySelector(t),x=t=>Array.from(document.querySelectorAll(t));function T(t){const e=typeof t=="string"?o(t):t;return!e||typeof bootstrap>"u"||!bootstrap.Modal?null:bootstrap.Modal.getOrCreateInstance(e)}let $=null;const C=new URLSearchParams(window.location.search);let k={bulan:C.get("bulan")||null,tahun:C.get("tahun")||null,jenis_surat:C.get("jenis_surat")||null},M=C.get("tanggal")||null;M&&document.addEventListener("DOMContentLoaded",()=>{const t=document.getElementById("tanggal");t&&(t.value=M)});function V(){if(document.getElementById("appToastStyles"))return;const t=document.createElement("style");t.id="appToastStyles",t.textContent=`
                .app-toast{
                    display:flex; align-items:flex-start; gap:12px;
                    min-width:320px; max-width:380px;
                    padding:14px 16px; margin-bottom:10px;
                    border:0; border-radius:16px; color:#fff;
                    box-shadow:0 12px 28px rgba(0,0,0,.18), 0 2px 6px rgba(0,0,0,.08);
                    position:relative; overflow:hidden;
                    opacity:0; transform:translateX(40px) scale(.96);
                    transition:opacity .45s cubic-bezier(.21,1.02,.73,1),
                               transform .45s cubic-bezier(.21,1.02,.73,1);
                }
                .app-toast.app-toast-in{ opacity:1; transform:translateX(0) scale(1); }
                .app-toast.app-toast-out{ opacity:0; transform:translateX(40px) scale(.96); }
                .app-toast-success{ background:linear-gradient(135deg,#16a34a,#22c55e); }
                .app-toast-error{ background:linear-gradient(135deg,#dc2626,#ef4444); }
                .app-toast-warning{ background:linear-gradient(135deg,#d97706,#f59e0b); }
                .app-toast-info{ background:linear-gradient(135deg,#2563eb,#3b82f6); }
                .app-toast-icon{
                    flex-shrink:0; width:34px; height:34px; border-radius:50%;
                    background:rgba(255,255,255,.22);
                    display:flex; align-items:center; justify-content:center;
                    font-size:17px; margin-top:1px;
                }
                .app-toast-body{ flex:1; font-size:.9rem; font-weight:600;
                    line-height:1.35; padding-top:4px; }
                .app-toast-close{
                    flex-shrink:0; background:transparent; border:0; color:#fff;
                    opacity:.85; font-size:1rem; line-height:1; padding:2px;
                    margin-top:2px; cursor:pointer;
                }
                .app-toast-close:hover{ opacity:1; }
                .app-toast-progress{
                    position:absolute; left:0; bottom:0; height:3px; width:100%;
                    background:rgba(255,255,255,.55); transform-origin:left;
                    animation:appToastShrink 3.5s linear forwards;
                }
                @keyframes appToastShrink{ from{transform:scaleX(1);} to{transform:scaleX(0);} }
            `,document.head.appendChild(t)}const u=(t,e="info")=>{var l;V();let a=document.getElementById("customToastContainer");a||(a=document.createElement("div"),a.id="customToastContainer",a.className="position-fixed top-0 end-0 p-3 d-flex flex-column align-items-end",a.style.zIndex="2000",document.body.appendChild(a));const r={success:["app-toast-success","bi-check-circle-fill"],error:["app-toast-error","bi-x-circle-fill"],warning:["app-toast-warning","bi-exclamation-triangle-fill"],info:["app-toast-info","bi-info-circle-fill"]},[n,i]=r[e]||r.info,s=document.createElement("div");s.className=`app-toast ${n}`,s.setAttribute("role","alert"),s.setAttribute("aria-live","assertive"),s.setAttribute("aria-atomic","true"),s.innerHTML=`
                <div class="app-toast-icon"><i class="bi ${i}"></i></div>
                <div class="app-toast-body">${t}</div>
                <button type="button" class="app-toast-close" aria-label="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="app-toast-progress"></div>
            `,a.appendChild(s),requestAnimationFrame(()=>{requestAnimationFrame(()=>{s.classList.add("app-toast-in")})});const d=()=>{s.classList.remove("app-toast-in"),s.classList.add("app-toast-out"),setTimeout(()=>{s.remove(),a.children.length||a.remove()},450)};(l=s.querySelector(".app-toast-close"))==null||l.addEventListener("click",d),setTimeout(d,3500)};async function v(t,e={}){const a={"X-Requested-With":"XMLHttpRequest",...e.method&&e.method!=="GET"?{"X-CSRF-TOKEN":z}:{},...e.headers},r=await fetch(t,{...e,headers:a});if(!r.ok){let n=r.statusText;try{n=(await r.json()).message||n}catch{}throw new Error(n||`HTTP Error: ${r.status}`)}return r}function w(t,e,a="warning"){const r=o(t);if(!r){u(e,a);return}r.className=`alert alert-${a} mb-3`,r.textContent=e,r.classList.remove("d-none")}function _(t){const e=o(t);e&&(e.classList.add("d-none"),e.textContent="")}function D(t){const e=t.split(".").pop().toLowerCase();return{pdf:"bi bi-file-earmark-pdf text-danger",doc:"bi bi-file-earmark-word text-primary",docx:"bi bi-file-earmark-word text-primary",xls:"bi bi-file-earmark-excel text-success",xlsx:"bi bi-file-earmark-excel text-success",jpg:"bi bi-file-image text-warning",jpeg:"bi bi-file-image text-warning",png:"bi bi-file-image text-warning"}[e]||"bi bi-file-earmark text-muted"}function q(t,e="lampiran"){try{const r=new URL(t,window.location.origin).pathname.split("/");return r.pop()||r.pop()||e}catch{return e}}async function P(){try{const e=await(await v("/surat/kode-surat-keluar",{headers:{Accept:"application/json"}})).json();return Array.isArray(e)?e.map(a=>({kode:a.kode,description:a.description||""})):[]}catch(t){return console.error("Gagal memuat daftar kode surat:",t),u("Gagal memuat daftar kode surat","error"),[]}}async function I({no_surat:t,instansi:e,tanggal_surat:a,exclude_id:r=""}){if(!t||!e||!a)return!1;try{const n=new URLSearchParams({no_surat:t,instansi:e,tanggal_surat:a});r&&n.append("exclude_id",r);const i=`/surat/cek-duplikat?${n.toString()}`,d=await(await v(i,{headers:{Accept:"application/json"}})).json();return typeof(d==null?void 0:d.exists)=="boolean"?d.exists:(u("Respon cek duplikat tidak valid dari server.","error"),!1)}catch(n){return console.error("Gagal cek duplikat surat:",n),u("Gagal mengecek duplikat surat.","error"),!1}}function H(t){const e=o(t);if(!e||e.dataset.hasTodayBtn)return;e.dataset.hasTodayBtn="1";let a=e.closest(".input-group");if(!a||a.querySelector(".btn-today-date"))return;const n=document.createElement("button");n.type="button",n.className="btn btn-outline-secondary btn-today-date",n.textContent="Hari Ini",n.addEventListener("click",()=>{const i=new Date,s=i.getFullYear(),d=String(i.getMonth()+1).padStart(2,"0"),l=String(i.getDate()).padStart(2,"0");e.value=`${s}-${d}-${l}`,e.dispatchEvent(new Event("change"))}),a.appendChild(n)}let F=null;function W(){var i,s,d,l,c;const t=new URLSearchParams,e=((s=(i=o("#searchInput"))==null?void 0:i.value)==null?void 0:s.trim())||"",a=((d=o("#jenis"))==null?void 0:d.value)||"",r=((l=o("#tanggal"))==null?void 0:l.value)||M||"",n=((c=o("#sort"))==null?void 0:c.value)||"";return r&&/^\d{4}(?:-\d{2}(?:-\d{2})?)?$/.test(r)&&t.append("tanggal",r),e&&t.append("search",e),a&&t.append("jenis",a),n&&t.append("sort",n),k.bulan&&!t.has("bulan")&&t.append("bulan",k.bulan),k.tahun&&!t.has("tahun")&&t.append("tahun",k.tahun),k.jenis_surat&&!t.has("jenis")&&!a&&t.append("jenis",k.jenis_surat),t.toString()}async function g(t="/surat"){try{const e=W(),a=t.split("?")[0]+(e?`?${e}`:""),n=await(await v(a,{headers:{Accept:"text/html"}})).text(),i=o("#tableContainer");i&&(i.innerHTML=n,Y(),B(),N())}catch(e){console.error(e);const a=o("#tableContainer");a&&(a.innerHTML=`<div class="alert alert-danger">Gagal memuat tabel. ${e.message||""}</div>`)}}function N(){const t=A;t.querySelectorAll(".btn-view").forEach(e=>{const a=e.cloneNode(!0);e.parentNode.replaceChild(a,e),a.addEventListener("click",r=>{r.preventDefault(),O(a.dataset.id)})}),t.querySelectorAll(".btn-edit").forEach(e=>{const a=e.cloneNode(!0);e.parentNode.replaceChild(a,e),a.addEventListener("click",r=>{r.preventDefault(),R(a.dataset.id)})}),t.querySelectorAll(".btn-delete").forEach(e=>{const a=e.cloneNode(!0);e.parentNode.replaceChild(a,e),a.addEventListener("click",r=>{var c,p;r.preventDefault();const n=a.dataset.id;o("#deleteSuratId").value=n;const i=a.closest("tr"),s=i?(p=(c=i.querySelector("td:nth-child(2)"))==null?void 0:c.textContent)==null?void 0:p.trim():n,d=o("#delete_no_surat_text");d&&(d.textContent=s||n);const l=T("#deleteSuratModal");l?l.show():console.warn("Bootstrap Modal not available.")})})}function J(){const t=o("#searchInput");t&&t.addEventListener("input",()=>{clearTimeout(F),F=setTimeout(()=>g(),400)}),["#jenis","#tanggal","#sort"].forEach(a=>{var r;return(r=o(a))==null?void 0:r.addEventListener("change",()=>g())});const e=o("#resetBtn");e&&e.addEventListener("click",a=>{a.preventDefault();const r=o("#sort"),n=(r==null?void 0:r.dataset.default)||"tanggal_terbaru";["#searchInput","#jenis","#tanggal","#sort"].forEach(i=>{const s=o(i);s&&(s.value=s.id==="sort"?n:"")}),k={bulan:null,tahun:null,jenis_surat:null},M=null,g()}),B()}function B(){x("button.reset-input").forEach(t=>{const e=t.cloneNode(!0);t.parentNode.replaceChild(e,t),e.addEventListener("click",a=>{a.preventDefault();const r=o(e.dataset.target);if(!r)return;let n="";r.dataset.default?n=r.dataset.default:r.id==="sort"&&(n="tanggal_terbaru"),r.value=n,r.id==="searchInput"?g():r.dispatchEvent(new Event("change"))})})}function Y(){A.querySelectorAll(".pagination a").forEach(t=>{const e=t.cloneNode(!0);t.parentNode.replaceChild(e,t),e.addEventListener("click",a=>{a.preventDefault(),e.href&&g(e.href)})})}async function Z(t=""){const e=o("#jenis_surat_add"),a=o("#kode-container-add");if(!e||!a)return;async function r(){const n=(e.value||"").trim().toLowerCase();if(!n){a.innerHTML=`
                    <label class="form-label">Kode Surat</label>
                    <input type="text"
                            id="kode_surat_add"
                            name="kode_surat"
                            class="form-control"
                            placeholder="Pilih jenis surat terlebih dahulu"
                            disabled>
                    <small class="text-muted">
                        Pilih jenis surat terlebih dahulu. Wajib diisi hanya untuk surat "Keluar".
                    </small>
                `;return}if(n==="keluar"){a.innerHTML=`
                    <label class="form-label">Kode Surat</label>
                    <select id="kode_surat_add" name="kode_surat" class="form-select" required>
                        <option value="">Memuat daftar kode...</option>
                    </select>
                    <small class="text-muted">Pilih kode surat yang sudah terdaftar di master kode.</small>
                `;const i=o("#kode_surat_add");if(!i)return;i.disabled=!0;const s=await P();i.innerHTML='<option value="">-- Pilih Kode Surat --</option>'+s.map(d=>`<option value="${d.kode}">${d.kode} - ${d.description||"-"}</option>`).join(""),t&&(i.value=t),i.disabled=!1;return}a.innerHTML=`
                <label class="form-label">Kode Surat</label>
                <input type="text"
                            id="kode_surat_add"
                            name="kode_surat"
                            class="form-control"
                            placeholder="Masukkan kode surat (optional)"
                            value="${t}">
                <small class="text-muted">
                    Opsional untuk surat masuk, wajib hanya jika jenis surat adalah "Keluar".
                </small>
            `}e.removeEventListener("change",e._kodeHandler),e._kodeHandler=r,e.addEventListener("change",e._kodeHandler),await r()}function Q(){const t=o("#addSuratForm");t==null||t.addEventListener("submit",async a=>{var c,p,h,b,L;a.preventDefault();const r=new FormData(t),n=t.querySelector("[type='submit']"),i=n==null?void 0:n.innerHTML;_("#addSuratAlert");const s=((c=r.get("no_surat"))==null?void 0:c.toString().trim())||"",d=((p=r.get("instansi"))==null?void 0:p.toString().trim())||"",l=((h=r.get("tanggal_surat"))==null?void 0:h.toString().trim())||"";try{if(await I({no_surat:s,instansi:d,tanggal_surat:l})){w("#addSuratAlert","Data dengan nomor surat, instansi, dan tanggal yang sama sudah ada.","warning"),(b=o("#add_no_surat"))==null||b.focus();return}n&&(n.disabled=!0,n.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');const m=await(await v("/surat",{method:"POST",body:r})).json();if(m!=null&&m.success){u(m.message||"Surat berhasil ditambahkan","success"),(L=bootstrap.Modal.getInstance(o("#addSuratModal")))==null||L.hide(),t.reset();const y=t.querySelector('input[type="file"]');y&&(y.value=""),_("#addSuratAlert"),g()}else{let y=(m==null?void 0:m.message)||"Gagal menyimpan surat. Periksa input Anda.";m!=null&&m.errors&&m.errors.no_surat&&m.errors.no_surat.length&&(y=m.errors.no_surat[0]),w("#addSuratAlert",y,"danger")}}catch(S){console.error("Error Tambah Surat:",S),w("#addSuratAlert",`Terjadi kesalahan: ${S.message||"Server error"}`,"danger")}finally{n&&(n.disabled=!1,n.innerHTML=i)}});const e=o("#addSuratModal");e&&e.addEventListener("show.bs.modal",async()=>{const a=o("#addSuratForm");if(a){a.reset();const r=a.querySelector('input[type="file"]');r&&(r.value="")}_("#addSuratAlert"),await Z(),H("#add_tanggal")})}async function K(t={},e=null){const a=o("#kode-container-edit");if(!a)return;a._kodeRendering&&(a._kodeAborted=!0),a._kodeRendering=!0,a._kodeAborted=!1;const r=o("#edit_jenis");if(r&&r._kodeHandler&&(r.removeEventListener("change",r._kodeHandler),r._kodeHandler=null),((t==null?void 0:t.jenis_surat)||(r==null?void 0:r.value)||"").toLowerCase()==="keluar"){const i=(t==null?void 0:t.kode_surat)||"";a.innerHTML=`
            <label class="form-label">Kode Surat</label>
            <select
                id="kode_input_edit"
                name="kode_surat"
                class="form-select"
                required>
                <option value="">Memuat kode...</option>
            </select>
            <small class="text-muted">Pilih kode surat untuk surat keluar.</small>
        `;const s=o("#kode_input_edit");if(!s){a._kodeRendering=!1;return}s.disabled=!0;const d=await P();if(a._kodeAborted){a._kodeRendering=!1;return}s.innerHTML='<option value="">-- Pilih Kode Surat --</option>'+d.map(l=>`
                <option
                    value="${l.kode}"
                    ${l.kode===i?"selected":""}>
                    ${l.kode} - ${l.description||"-"}
                </option>
            `).join(""),s.disabled=!1}else{let i="";const s=e||t;s!=null&&s.jenis_surat&&s.jenis_surat.toLowerCase()==="masuk"&&(i=(s==null?void 0:s.kode_surat)||""),a.innerHTML=`
            <label class="form-label">Kode Surat</label>
            <input
                type="text"
                id="kode_input_edit"
                name="kode_surat"
                class="form-control"
                value="${i}"
                placeholder="Masukkan kode surat (opsional)">
            <small class="text-muted">
                Kode surat dapat diisi manual untuk surat masuk.
            </small>
        `}a._kodeRendering=!1,r&&(r._kodeHandler=async function(){await K({jenis_surat:this.value},e||t)},r.addEventListener("change",r._kodeHandler))}async function R(t){var e;try{const r=await(await v(`/surat/${t}`)).json();if(!(r!=null&&r.success))return u("Gagal memuat data surat","error");const n=r.surat,i=o("#editSuratForm");if(_("#editSuratAlert"),i){i.querySelectorAll('input[name="hapus_file[]"]').forEach(c=>c.remove()),i.querySelectorAll(".file-item").forEach(c=>{c.classList.remove("bg-danger-subtle","text-decoration-line-through");const p=c.querySelector(".btn-delete-old-file");if(p){p.classList.remove("btn-danger"),p.classList.add("btn-outline-danger");const h=p.querySelector("i");h&&(h.className="bi bi-trash"),p.title="Tandai untuk dihapus"}});const l=i.querySelector('input[type="file"]');l&&(l.value="")}o("#edit_id").value=n.id??"",o("#edit_no_surat").value=n.no_surat||"",o("#edit_jenis").value=n.jenis_surat||"",o("#edit_tanggal").value=n.tanggal_surat_raw||"",o("#edit_pengirim").value=n.pengirim||"",o("#edit_penerima").value=n.penerima||"",o("#edit_perihal").value=n.perihal||"";const s=o("#edit_instansi");s&&(s.value=n.instansi||""),await K(n);const d=o("#edit_file_list");d&&(d.innerHTML="",Array.isArray(n.files)&&n.files.length?n.files.forEach(l=>{const c=document.createElement("div");c.className="d-flex align-items-center justify-content-between p-2 border rounded file-item mb-2",c.dataset.path=l.path,c.innerHTML=`
                            <span>
                                <i class="${D(l.name)} me-2 fs-5"></i>
                                <a href="${l.url}" target="_blank" class="text-decoration-none">${l.name}</a>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-old-file" 
                                data-path="${l.path}" title="Tandai untuk dihapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        `,d.appendChild(c)}):d.innerHTML='<p class="text-muted small mb-0">Belum ada file terlampir</p>'),H("#edit_tanggal"),(e=T("#editSuratModal"))==null||e.show()}catch(a){console.error(a),u("Gagal memuat data edit","error")}}function ee(){const t=o("#editSuratForm");t==null||t.addEventListener("submit",async e=>{var c,p,h,b,L,S;e.preventDefault();const a=new FormData(t),r=a.get("id")||((c=o("#edit_id"))==null?void 0:c.value);if(!r)return u("ID surat tidak ditemukan","error");_("#editSuratAlert");const n=t.querySelector("[type='submit']"),i=n==null?void 0:n.innerHTML,s=((p=a.get("no_surat"))==null?void 0:p.toString().trim())||"",d=((h=a.get("instansi"))==null?void 0:h.toString().trim())||"",l=((b=a.get("tanggal_surat"))==null?void 0:b.toString().trim())||"";try{if(await I({no_surat:s,instansi:d,tanggal_surat:l,exclude_id:r})){w("#editSuratAlert","Data dengan nomor surat, instansi, dan tanggal yang sama sudah ada.","warning"),(L=o("#edit_no_surat"))==null||L.focus();return}n&&(n.disabled=!0,n.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');const m=a.get("jenis_surat"),y=((S=document.getElementById("kode_input_edit"))==null?void 0:S.value)||"";a.delete("kode_surat"),a.append("kode_surat",y),a.append("_method","PUT");const f=await(await v(`/surat/${r}`,{method:"POST",body:a})).json();if(f!=null&&f.success)u(f.message||"Surat berhasil diperbarui","success"),setTimeout(()=>{var E;(E=bootstrap.Modal.getInstance(o("#editSuratModal")))==null||E.hide(),_("#editSuratAlert"),g()},400);else{let E=(f==null?void 0:f.message)||"Gagal memperbarui surat. Periksa input Anda.";f!=null&&f.errors&&f.errors.no_surat&&f.errors.no_surat.length&&(E=f.errors.no_surat[0]),w("#editSuratAlert",E,"danger")}}catch(j){console.error("Error Edit Surat:",j),w("#editSuratAlert",`Terjadi kesalahan: ${j.message||"Server error"}`,"danger")}finally{n&&(n.disabled=!1,n.innerHTML=i)}})}function te(){document.addEventListener("click",function(t){var d;const e=t.target.closest(".btn-delete-old-file");if(!e)return;t.preventDefault();const a=e.dataset.path,r=e.closest(".file-item"),n=o("#editSuratForm");if(!a||!r||!n)return;let i=n.querySelector(`input[name="hapus_file[]"][value="${a}"]`);const s=((d=r.querySelector("a"))==null?void 0:d.textContent)||"";if(r.classList.contains("bg-danger-subtle")){r.classList.remove("bg-danger-subtle","text-decoration-line-through"),e.classList.remove("btn-danger"),e.classList.add("btn-outline-danger");const l=e.querySelector("i");l&&(l.className="bi bi-trash"),e.title="Tandai untuk dihapus",i&&i.remove(),u(`Penghapusan "${s}" dibatalkan.`,"info")}else{r.classList.add("bg-danger-subtle","text-decoration-line-through"),e.classList.remove("btn-outline-danger"),e.classList.add("btn-danger");const l=e.querySelector("i");l&&(l.className="bi bi-arrow-counterclockwise"),e.title="Batalkan Penghapusan";const c=document.createElement("input");c.type="hidden",c.name="hapus_file[]",c.value=a,n.appendChild(c),u(`"${s}" ditandai untuk dihapus saat disimpan.`,"warning")}})}function ae(){const t=o("#deleteSuratForm");t==null||t.addEventListener("submit",async e=>{var i,s;e.preventDefault();const a=(i=o("#deleteSuratId"))==null?void 0:i.value;if(!a)return u("ID surat tidak ditemukan","error");const r=t.querySelector("[type='submit']"),n=r==null?void 0:r.innerHTML;try{r&&(r.disabled=!0,r.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...');const l=await(await v(`/surat/${a}`,{method:"DELETE"})).json();l!=null&&l.success?(u(l.message||"Surat berhasil dihapus","success"),(s=bootstrap.Modal.getInstance(o("#deleteSuratModal")))==null||s.hide(),g()):u((l==null?void 0:l.message)||"Gagal menghapus surat","error")}catch(d){console.error("Error Hapus Surat:",d),u(`Terjadi kesalahan: ${d.message||"Server error"}`,"error")}finally{r&&(r.disabled=!1,r.innerHTML=n)}})}function G(){const t=x("#view_file .file-select-check"),e=t.filter(i=>i.checked),a=o("#lampiran_count");a&&(a.textContent=`${t.length} file, ${e.length} dipilih`);const r=o("#btnPrintSelected"),n=o("#btnDownloadSelected");r&&(r.disabled=e.length!==1),n&&(n.disabled=e.length===0)}function U(t){var n;if(!t.length){u("Tidak ada file lampiran yang bisa diunduh.","warning");return}if(!$){u("ID surat tidak diketahui.","error");return}const e=document.createElement("form");e.method="POST",e.action="/surat/download-multiple",e.style.display="none";const a=(n=document.querySelector('meta[name="csrf-token"]'))==null?void 0:n.getAttribute("content");if(a){const i=document.createElement("input");i.type="hidden",i.name="_token",i.value=a,e.appendChild(i)}const r=document.createElement("input");r.type="hidden",r.name="surat_id",r.value=$,e.appendChild(r),t.forEach(i=>{const s=document.createElement("input");s.type="hidden",s.name="paths[]",s.value=i,e.appendChild(s)}),document.body.appendChild(e),e.submit(),setTimeout(()=>e.remove(),2e3)}async function O(t){var e;try{$=t;const r=await(await v(`/surat/${t}`)).json();if(!(r!=null&&r.success))return u("Gagal memuat detail surat","error");const n=r.surat;o("#view_no_surat").textContent=n.no_surat||"-",o("#view_kode").textContent=n.kode_keterangan?`${n.kode_surat} - ${n.kode_keterangan}`:n.kode_surat||"-";const i=o("#view_jenis");i&&(i.innerHTML=n.jenis_surat==="Masuk"?`<span class="badge bg-info">
                    <i class="bi bi-box-arrow-in-down me-1"></i>
                    Masuk
               </span>`:`<span class="badge bg-success">
                    <i class="bi bi-box-arrow-up-right me-1"></i>
                    Keluar
               </span>`),o("#view_tanggal").textContent=n.tanggal_surat||"-",o("#view_instansi").textContent=n.instansi||"-",o("#view_pengirim").textContent=n.pengirim||"-",o("#view_penerima").textContent=n.penerima||"-",o("#view_perihal").textContent=n.perihal||"-",o("#view_keterangan").textContent=n.keterangan||"-";const s=o("#view_file");o("#view_created_by").textContent=n.created_by||"-",o("#view_created_at").textContent=n.created_at||"-",o("#view_updated_by").textContent=n.updated_by||"-",o("#view_updated_at").textContent=n.updated_at||"-";const d=o("#view_status");d&&(n.status==="Pernah Diubah"?d.innerHTML=`
            <span class="badge bg-warning text-dark">
                <i class="bi bi-pencil-square me-1"></i>
                Pernah Diubah
            </span>
        `:d.innerHTML=`
            <span class="badge bg-success">
                <i class="bi bi-check-circle me-1"></i>
                Belum Pernah Diubah
            </span>
        `),s&&(s.innerHTML="",Array.isArray(n.files)&&n.files.length?n.files.forEach(l=>{const c=document.createElement("div");c.className="list-group-item d-flex justify-content-between align-items-center",c.innerHTML=`
                            <div class="d-flex align-items-center">
                                <input type="checkbox"
                                       class="form-check-input me-2 file-select-check"
                                       data-path="${l.path}"
                                       data-url="${l.url}">
                                <i class="${D(l.name)} me-2 fs-5"></i>
                                <span class="text-break">${l.name}</span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button"
                                        class="btn btn-outline-primary btn-preview-file"
                                        data-url="${l.url}"
                                        title="Pratinjau">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="${l.url}"
                                   class="btn btn-outline-success btn-direct-download"
                                   data-filename="${l.name}"
                                   title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        `,s.appendChild(c)}):s.innerHTML=`
                        <div class="list-group-item text-muted small">
                            Belum ada file terlampir.
                        </div>`),G(),(e=T("#viewSuratModal"))==null||e.show()}catch(a){console.error(a),u("Gagal menampilkan surat","error")}}function ne(t){var d;if(!t)return;const e=o("#filePreviewBody"),a=o("#file-loading-spinner"),r=o("#previewDownloadLink");if(a&&(a.style.display="flex"),e&&(e.innerHTML=""),r){r.href=t;const l=q(t,"lampiran");r.setAttribute("download",l);const c=o("#preview_filename_label");c&&(c.textContent=l)}const n=t.split(".").pop().toLowerCase();let i;const s=()=>{a&&(a.style.display="none")};["pdf"].includes(n)?(i=document.createElement("iframe"),i.className="w-100",i.style.minHeight="80vh",i.onload=s,i.onerror=s,i.src=t):["jpg","jpeg","png"].includes(n)?(i=document.createElement("img"),i.className="img-fluid",i.onload=s,i.onerror=s,i.src=t):(i=document.createElement("div"),i.className="p-3",i.innerHTML=`
                <p class="mb-1">Pratinjau langsung tidak tersedia.</p>
                <a href="${t}" target="_blank">Klik di sini untuk membuka / mengunduh file.</a>
            `,s()),e&&e.appendChild(i),(d=T("#filePreviewModal"))==null||d.show()}function re(){var t,e,a,r;(t=o("#viewSuratModal"))==null||t.addEventListener("change",n=>{n.target.closest(".file-select-check")&&G()}),document.addEventListener("click",n=>{const i=n.target.closest(".btn-preview-file");if(!i)return;const s=i.dataset.url;s&&ne(s)}),document.addEventListener("click",n=>{const i=n.target.closest(".btn-direct-download");if(!i)return;n.preventDefault();const s=i.getAttribute("href");if(!s){u("URL file tidak ditemukan.","error");return}let d=i.dataset.filename||"lampiran";const l=document.createElement("a");l.href=s,l.download=d,document.body.appendChild(l),l.click(),l.remove()}),(e=o("#btnPrintSelected"))==null||e.addEventListener("click",n=>{n.preventDefault();const i=x("#view_file .file-select-check:checked");if(i.length!==1){u("Pilih tepat satu file yang akan diprint.","warning");return}const s=i[0].dataset.url;if(!s)return;const d=window.open(s,"_blank");d&&d.addEventListener("load",()=>{try{d.print()}catch{}})}),(a=o("#btnDownloadSelected"))==null||a.addEventListener("click",n=>{n.preventDefault();const i=x("#view_file .file-select-check:checked");if(!i.length){u("Pilih minimal satu file lampiran.","warning");return}if(i.length===1){const d=i[0],l=d.dataset.url;if(!l){u("URL file tidak ditemukan.","error");return}let c="lampiran";const p=d.closest(".list-group-item"),h=p==null?void 0:p.querySelector(".text-break");h&&h.textContent.trim()?c=h.textContent.trim():c=q(l,c);const b=document.createElement("a");b.href=l,b.download=c,document.body.appendChild(b),b.click(),b.remove();return}const s=i.map(d=>d.dataset.path).filter(d=>typeof d=="string"&&d.length>0);U(s)}),(r=o("#btnDownloadAll"))==null||r.addEventListener("click",n=>{n.preventDefault();const i=x("#view_file .file-select-check");if(!i.length){u("Tidak ada file lampiran.","warning");return}const s=i.map(d=>d.dataset.path).filter(d=>typeof d=="string"&&d.length>0);U(s)})}document.addEventListener("DOMContentLoaded",()=>{["resetJenis","resetSort"].forEach(t=>{var e;(e=document.getElementById(t))==null||e.addEventListener("click",()=>{if(k={bulan:null,tahun:null,jenis_surat:null},t==="resetJenis"){const a=document.getElementById("jenis");a&&(a.value="")}g()})})}),document.addEventListener("DOMContentLoaded",()=>{J(),g(),N(),Q(),ee(),te(),ae(),re(),H("#edit_tanggal"),window.SuratApp={loadTable:g,loadSuratTable:g,viewSurat:O,editSurat:R}})})();
