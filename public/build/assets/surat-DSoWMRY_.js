window.SuratApp?console.warn("[surat.js] SuratApp sudah terdaftar, skip re-init."):(()=>{var z;if(!document.querySelector("#page-surat"))return;const A=document.querySelector("#tableContainer");if(!A)return;const O=((z=document.querySelector('meta[name="csrf-token"]'))==null?void 0:z.getAttribute("content"))||"",o=e=>document.querySelector(e),x=e=>Array.from(document.querySelectorAll(e));function T(e){const t=typeof e=="string"?o(e):e;return!t||typeof bootstrap>"u"||!bootstrap.Modal?null:bootstrap.Modal.getOrCreateInstance(t)}let $=null;const C=new URLSearchParams(window.location.search);let k={bulan:C.get("bulan")||null,tahun:C.get("tahun")||null,jenis_surat:C.get("jenis_surat")||null},M=C.get("tanggal")||null;M&&document.addEventListener("DOMContentLoaded",()=>{const e=document.getElementById("tanggal");e&&(e.value=M)});function V(){if(document.getElementById("appToastStyles"))return;const e=document.createElement("style");e.id="appToastStyles",e.textContent=`
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
            `,document.head.appendChild(e)}const u=(e,t="info")=>{var d;V();let a=document.getElementById("customToastContainer");a||(a=document.createElement("div"),a.id="customToastContainer",a.className="position-fixed top-0 end-0 p-3 d-flex flex-column align-items-end",a.style.zIndex="2000",document.body.appendChild(a));const r={success:["app-toast-success","bi-check-circle-fill"],error:["app-toast-error","bi-x-circle-fill"],warning:["app-toast-warning","bi-exclamation-triangle-fill"],info:["app-toast-info","bi-info-circle-fill"]},[n,i]=r[t]||r.info,s=document.createElement("div");s.className=`app-toast ${n}`,s.setAttribute("role","alert"),s.setAttribute("aria-live","assertive"),s.setAttribute("aria-atomic","true"),s.innerHTML=`
                <div class="app-toast-icon"><i class="bi ${i}"></i></div>
                <div class="app-toast-body">${e}</div>
                <button type="button" class="app-toast-close" aria-label="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="app-toast-progress"></div>
            `,a.appendChild(s),requestAnimationFrame(()=>{requestAnimationFrame(()=>{s.classList.add("app-toast-in")})});const l=()=>{s.classList.remove("app-toast-in"),s.classList.add("app-toast-out"),setTimeout(()=>{s.remove(),a.children.length||a.remove()},450)};(d=s.querySelector(".app-toast-close"))==null||d.addEventListener("click",l),setTimeout(l,3500)};async function v(e,t={}){const a={"X-Requested-With":"XMLHttpRequest",...t.method&&t.method!=="GET"?{"X-CSRF-TOKEN":O}:{},...t.headers},r=await fetch(e,{...t,headers:a});if(!r.ok){let n=r.statusText;try{n=(await r.json()).message||n}catch{}throw new Error(n||`HTTP Error: ${r.status}`)}return r}function _(e,t,a="warning"){const r=o(e);if(!r){u(t,a);return}r.className=`alert alert-${a} mb-3`,r.textContent=t,r.classList.remove("d-none")}function w(e){const t=o(e);t&&(t.classList.add("d-none"),t.textContent="")}function H(e){const t=e.split(".").pop().toLowerCase();return{pdf:"bi bi-file-earmark-pdf text-danger",doc:"bi bi-file-earmark-word text-primary",docx:"bi bi-file-earmark-word text-primary",xls:"bi bi-file-earmark-excel text-success",xlsx:"bi bi-file-earmark-excel text-success",jpg:"bi bi-file-image text-warning",jpeg:"bi bi-file-image text-warning",png:"bi bi-file-image text-warning"}[t]||"bi bi-file-earmark text-muted"}function I(e,t="lampiran"){try{const r=new URL(e,window.location.origin).pathname.split("/");return r.pop()||r.pop()||t}catch{return t}}async function P(){try{const t=await(await v("/surat/kode-surat-keluar",{headers:{Accept:"application/json"}})).json();return Array.isArray(t)?t.map(a=>({kode:a.kode,description:a.description||""})):[]}catch(e){return console.error("Gagal memuat daftar kode surat:",e),u("Gagal memuat daftar kode surat","error"),[]}}async function q({no_surat:e,instansi:t,tanggal_surat:a,exclude_id:r=""}){if(!e||!t||!a)return!1;try{const n=new URLSearchParams({no_surat:e,instansi:t,tanggal_surat:a});r&&n.append("exclude_id",r);const i=`/surat/cek-duplikat?${n.toString()}`,l=await(await v(i,{headers:{Accept:"application/json"}})).json();return typeof(l==null?void 0:l.exists)=="boolean"?l.exists:(u("Respon cek duplikat tidak valid dari server.","error"),!1)}catch(n){return console.error("Gagal cek duplikat surat:",n),u("Gagal mengecek duplikat surat.","error"),!1}}function D(e){const t=o(e);if(!t||t.dataset.hasTodayBtn)return;t.dataset.hasTodayBtn="1";let a=t.closest(".input-group");if(!a||a.querySelector(".btn-today-date"))return;const n=document.createElement("button");n.type="button",n.className="btn btn-outline-secondary btn-today-date",n.textContent="Hari Ini",n.addEventListener("click",()=>{const i=new Date,s=i.getFullYear(),l=String(i.getMonth()+1).padStart(2,"0"),d=String(i.getDate()).padStart(2,"0");t.value=`${s}-${l}-${d}`,t.dispatchEvent(new Event("change"))}),a.appendChild(n)}let F=null;function Z(){var i,s,l,d,c;const e=new URLSearchParams,t=((s=(i=o("#searchInput"))==null?void 0:i.value)==null?void 0:s.trim())||"",a=((l=o("#jenis"))==null?void 0:l.value)||"",r=((d=o("#tanggal"))==null?void 0:d.value)||M||"",n=((c=o("#sort"))==null?void 0:c.value)||"";return r&&/^\d{4}(?:-\d{2}(?:-\d{2})?)?$/.test(r)&&e.append("tanggal",r),t&&e.append("search",t),a&&e.append("jenis",a),n&&e.append("sort",n),k.bulan&&!e.has("bulan")&&e.append("bulan",k.bulan),k.tahun&&!e.has("tahun")&&e.append("tahun",k.tahun),k.jenis_surat&&!e.has("jenis")&&!a&&e.append("jenis",k.jenis_surat),e.toString()}async function g(e="/surat"){try{const t=Z(),a=e.split("?")[0]+(t?`?${t}`:""),n=await(await v(a,{headers:{Accept:"text/html"}})).text(),i=o("#tableContainer");i&&(i.innerHTML=n,J(),R(),N())}catch(t){console.error(t);const a=o("#tableContainer");a&&(a.innerHTML=`<div class="alert alert-danger">Gagal memuat tabel. ${t.message||""}</div>`)}}function N(){const e=A;e.querySelectorAll(".btn-view").forEach(t=>{const a=t.cloneNode(!0);t.parentNode.replaceChild(a,t),a.addEventListener("click",r=>{r.preventDefault(),X(a.dataset.id)})}),e.querySelectorAll(".btn-edit").forEach(t=>{const a=t.cloneNode(!0);t.parentNode.replaceChild(a,t),a.addEventListener("click",r=>{r.preventDefault(),K(a.dataset.id)})}),e.querySelectorAll(".btn-delete").forEach(t=>{const a=t.cloneNode(!0);t.parentNode.replaceChild(a,t),a.addEventListener("click",r=>{var c,p;r.preventDefault();const n=a.dataset.id;o("#deleteSuratId").value=n;const i=a.closest("tr"),s=i?(p=(c=i.querySelector("td:nth-child(2)"))==null?void 0:c.textContent)==null?void 0:p.trim():n,l=o("#delete_no_surat_text");l&&(l.textContent=s||n);const d=T("#deleteSuratModal");d?d.show():console.warn("Bootstrap Modal not available.")})})}function W(){const e=o("#searchInput");e&&e.addEventListener("input",()=>{clearTimeout(F),F=setTimeout(()=>g(),400)}),["#jenis","#tanggal","#sort"].forEach(a=>{var r;return(r=o(a))==null?void 0:r.addEventListener("change",()=>g())});const t=o("#resetBtn");t&&t.addEventListener("click",a=>{a.preventDefault();const r=o("#sort"),n=(r==null?void 0:r.dataset.default)||"tanggal_terbaru";["#searchInput","#jenis","#tanggal","#sort"].forEach(i=>{const s=o(i);s&&(s.value=s.id==="sort"?n:"")}),k={bulan:null,tahun:null,jenis_surat:null},M=null,g()}),R()}function R(){x("button.reset-input").forEach(e=>{const t=e.cloneNode(!0);e.parentNode.replaceChild(t,e),t.addEventListener("click",a=>{a.preventDefault();const r=o(t.dataset.target);if(!r)return;let n="";r.dataset.default?n=r.dataset.default:r.id==="sort"&&(n="tanggal_terbaru"),r.value=n,r.id==="searchInput"?g():r.dispatchEvent(new Event("change"))})})}function J(){A.querySelectorAll(".pagination a").forEach(e=>{const t=e.cloneNode(!0);e.parentNode.replaceChild(t,e),t.addEventListener("click",a=>{a.preventDefault(),t.href&&g(t.href)})})}async function Y(e=""){const t=o("#jenis_surat_add"),a=o("#kode-container-add");if(!t||!a)return;async function r(){const n=(t.value||"").trim().toLowerCase();if(!n){a.innerHTML=`
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
                `;const i=o("#kode_surat_add");if(!i)return;i.disabled=!0;const s=await P();i.innerHTML='<option value="">-- Pilih Kode Surat --</option>'+s.map(l=>`<option value="${l.kode}">${l.kode} - ${l.description||"-"}</option>`).join(""),e&&(i.value=e),i.disabled=!1;return}a.innerHTML=`
                <label class="form-label">Kode Surat</label>
                <input type="text"
                            id="kode_surat_add"
                            name="kode_surat"
                            class="form-control"
                            placeholder="Masukkan kode surat (optional)"
                            value="${e}">
                <small class="text-muted">
                    Opsional untuk surat masuk, wajib hanya jika jenis surat adalah "Keluar".
                </small>
            `}t.removeEventListener("change",t._kodeHandler),t._kodeHandler=r,t.addEventListener("change",t._kodeHandler),await r()}function Q(){const e=o("#addSuratForm");e==null||e.addEventListener("submit",async a=>{var c,p,h,b,E;a.preventDefault();const r=new FormData(e),n=e.querySelector("[type='submit']"),i=n==null?void 0:n.innerHTML;w("#addSuratAlert");const s=((c=r.get("no_surat"))==null?void 0:c.toString().trim())||"",l=((p=r.get("instansi"))==null?void 0:p.toString().trim())||"",d=((h=r.get("tanggal_surat"))==null?void 0:h.toString().trim())||"";try{if(await q({no_surat:s,instansi:l,tanggal_surat:d})){_("#addSuratAlert","Data dengan nomor surat, instansi, dan tanggal yang sama sudah ada.","warning"),(b=o("#add_no_surat"))==null||b.focus();return}n&&(n.disabled=!0,n.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');const m=await(await v("/surat",{method:"POST",body:r})).json();if(m!=null&&m.success){u(m.message||"Surat berhasil ditambahkan","success"),(E=bootstrap.Modal.getInstance(o("#addSuratModal")))==null||E.hide(),e.reset();const y=e.querySelector('input[type="file"]');y&&(y.value=""),w("#addSuratAlert"),g()}else{let y=(m==null?void 0:m.message)||"Gagal menyimpan surat. Periksa input Anda.";m!=null&&m.errors&&m.errors.no_surat&&m.errors.no_surat.length&&(y=m.errors.no_surat[0]),_("#addSuratAlert",y,"danger")}}catch(S){console.error("Error Tambah Surat:",S),_("#addSuratAlert",`Terjadi kesalahan: ${S.message||"Server error"}`,"danger")}finally{n&&(n.disabled=!1,n.innerHTML=i)}});const t=o("#addSuratModal");t&&t.addEventListener("show.bs.modal",async()=>{const a=o("#addSuratForm");if(a){a.reset();const r=a.querySelector('input[type="file"]');r&&(r.value="")}w("#addSuratAlert"),await Y(),D("#add_tanggal")})}async function B(e={},t=null){const a=o("#kode-container-edit");if(!a)return;a._kodeRendering&&(a._kodeAborted=!0),a._kodeRendering=!0,a._kodeAborted=!1;const r=o("#edit_jenis");if(r&&r._kodeHandler&&(r.removeEventListener("change",r._kodeHandler),r._kodeHandler=null),((e==null?void 0:e.jenis_surat)||(r==null?void 0:r.value)||"").toLowerCase()==="keluar"){const i=(e==null?void 0:e.kode_surat)||"";a.innerHTML=`
            <label class="form-label">Kode Surat</label>
            <select
                id="kode_input_edit"
                name="kode_surat"
                class="form-select"
                required>
                <option value="">Memuat kode...</option>
            </select>
            <small class="text-muted">Pilih kode surat untuk surat keluar.</small>
        `;const s=o("#kode_input_edit");if(!s){a._kodeRendering=!1;return}s.disabled=!0;const l=await P();if(a._kodeAborted){a._kodeRendering=!1;return}s.innerHTML='<option value="">-- Pilih Kode Surat --</option>'+l.map(d=>`
                <option
                    value="${d.kode}"
                    ${d.kode===i?"selected":""}>
                    ${d.kode} - ${d.description||"-"}
                </option>
            `).join(""),s.disabled=!1}else{let i="";const s=t||e;s!=null&&s.jenis_surat&&s.jenis_surat.toLowerCase()==="masuk"&&(i=(s==null?void 0:s.kode_surat)||""),a.innerHTML=`
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
        `}a._kodeRendering=!1,r&&(r._kodeHandler=async function(){await B({jenis_surat:this.value},t||e)},r.addEventListener("change",r._kodeHandler))}async function K(e){var t;try{const r=await(await v(`/surat/${e}`)).json();if(!(r!=null&&r.success))return u("Gagal memuat data surat","error");const n=r.surat,i=o("#editSuratForm");if(w("#editSuratAlert"),i){i.querySelectorAll('input[name="hapus_file[]"]').forEach(c=>c.remove()),i.querySelectorAll(".file-item").forEach(c=>{c.classList.remove("bg-danger-subtle","text-decoration-line-through");const p=c.querySelector(".btn-delete-old-file");if(p){p.classList.remove("btn-danger"),p.classList.add("btn-outline-danger");const h=p.querySelector("i");h&&(h.className="bi bi-trash"),p.title="Tandai untuk dihapus"}});const d=i.querySelector('input[type="file"]');d&&(d.value="")}o("#edit_id").value=n.id??"",o("#edit_no_surat").value=n.no_surat||"",o("#edit_jenis").value=n.jenis_surat||"",o("#edit_tanggal").value=n.tanggal_surat_raw||"",o("#edit_pengirim").value=n.pengirim||"",o("#edit_penerima").value=n.penerima||"",o("#edit_perihal").value=n.perihal||"";const s=o("#edit_instansi");s&&(s.value=n.instansi||""),await B(n);const l=o("#edit_file_list");l&&(l.innerHTML="",Array.isArray(n.files)&&n.files.length?n.files.forEach(d=>{const c=document.createElement("div");c.className="d-flex align-items-center justify-content-between p-2 border rounded file-item mb-2",c.dataset.path=d.path,c.innerHTML=`
                            <span>
                                <i class="${H(d.name)} me-2 fs-5"></i>
                                <a href="${d.url}" target="_blank" class="text-decoration-none">${d.name}</a>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-old-file" 
                                data-path="${d.path}" title="Tandai untuk dihapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        `,l.appendChild(c)}):l.innerHTML='<p class="text-muted small mb-0">Belum ada file terlampir</p>'),D("#edit_tanggal"),(t=T("#editSuratModal"))==null||t.show()}catch(a){console.error(a),u("Gagal memuat data edit","error")}}function ee(){const e=o("#editSuratForm");e==null||e.addEventListener("submit",async t=>{var c,p,h,b,E,S;t.preventDefault();const a=new FormData(e),r=a.get("id")||((c=o("#edit_id"))==null?void 0:c.value);if(!r)return u("ID surat tidak ditemukan","error");w("#editSuratAlert");const n=e.querySelector("[type='submit']"),i=n==null?void 0:n.innerHTML,s=((p=a.get("no_surat"))==null?void 0:p.toString().trim())||"",l=((h=a.get("instansi"))==null?void 0:h.toString().trim())||"",d=((b=a.get("tanggal_surat"))==null?void 0:b.toString().trim())||"";try{if(await q({no_surat:s,instansi:l,tanggal_surat:d,exclude_id:r})){_("#editSuratAlert","Data dengan nomor surat, instansi, dan tanggal yang sama sudah ada.","warning"),(E=o("#edit_no_surat"))==null||E.focus();return}n&&(n.disabled=!0,n.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');const m=a.get("jenis_surat"),y=((S=document.getElementById("kode_input_edit"))==null?void 0:S.value)||"";a.delete("kode_surat"),a.append("kode_surat",y),a.append("_method","PUT");const f=await(await v(`/surat/${r}`,{method:"POST",body:a})).json();if(f!=null&&f.success)u(f.message||"Surat berhasil diperbarui","success"),setTimeout(()=>{var L;(L=bootstrap.Modal.getInstance(o("#editSuratModal")))==null||L.hide(),w("#editSuratAlert"),g()},400);else{let L=(f==null?void 0:f.message)||"Gagal memperbarui surat. Periksa input Anda.";f!=null&&f.errors&&f.errors.no_surat&&f.errors.no_surat.length&&(L=f.errors.no_surat[0]),_("#editSuratAlert",L,"danger")}}catch(j){console.error("Error Edit Surat:",j),_("#editSuratAlert",`Terjadi kesalahan: ${j.message||"Server error"}`,"danger")}finally{n&&(n.disabled=!1,n.innerHTML=i)}})}function te(){document.addEventListener("click",function(e){var l;const t=e.target.closest(".btn-delete-old-file");if(!t)return;e.preventDefault();const a=t.dataset.path,r=t.closest(".file-item"),n=o("#editSuratForm");if(!a||!r||!n)return;let i=n.querySelector(`input[name="hapus_file[]"][value="${a}"]`);const s=((l=r.querySelector("a"))==null?void 0:l.textContent)||"";if(r.classList.contains("bg-danger-subtle")){r.classList.remove("bg-danger-subtle","text-decoration-line-through"),t.classList.remove("btn-danger"),t.classList.add("btn-outline-danger");const d=t.querySelector("i");d&&(d.className="bi bi-trash"),t.title="Tandai untuk dihapus",i&&i.remove(),u(`Penghapusan "${s}" dibatalkan.`,"info")}else{r.classList.add("bg-danger-subtle","text-decoration-line-through"),t.classList.remove("btn-outline-danger"),t.classList.add("btn-danger");const d=t.querySelector("i");d&&(d.className="bi bi-arrow-counterclockwise"),t.title="Batalkan Penghapusan";const c=document.createElement("input");c.type="hidden",c.name="hapus_file[]",c.value=a,n.appendChild(c),u(`"${s}" ditandai untuk dihapus saat disimpan.`,"warning")}})}function ae(){const e=o("#deleteSuratForm");e==null||e.addEventListener("submit",async t=>{var i,s;t.preventDefault();const a=(i=o("#deleteSuratId"))==null?void 0:i.value;if(!a)return u("ID surat tidak ditemukan","error");const r=e.querySelector("[type='submit']"),n=r==null?void 0:r.innerHTML;try{r&&(r.disabled=!0,r.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...');const d=await(await v(`/surat/${a}`,{method:"DELETE"})).json();d!=null&&d.success?(u(d.message||"Surat berhasil dihapus","success"),(s=bootstrap.Modal.getInstance(o("#deleteSuratModal")))==null||s.hide(),g()):u((d==null?void 0:d.message)||"Gagal menghapus surat","error")}catch(l){console.error("Error Hapus Surat:",l),u(`Terjadi kesalahan: ${l.message||"Server error"}`,"error")}finally{r&&(r.disabled=!1,r.innerHTML=n)}})}function G(){const e=x("#view_file .file-select-check"),t=e.filter(i=>i.checked),a=o("#lampiran_count");a&&(a.textContent=`${e.length} file, ${t.length} dipilih`);const r=o("#btnPrintSelected"),n=o("#btnDownloadSelected");r&&(r.disabled=t.length!==1),n&&(n.disabled=t.length===0)}function U(e){var n;if(!e.length){u("Tidak ada file lampiran yang bisa diunduh.","warning");return}if(!$){u("ID surat tidak diketahui.","error");return}const t=document.createElement("form");t.method="POST",t.action="/surat/download-multiple",t.style.display="none";const a=(n=document.querySelector('meta[name="csrf-token"]'))==null?void 0:n.getAttribute("content");if(a){const i=document.createElement("input");i.type="hidden",i.name="_token",i.value=a,t.appendChild(i)}const r=document.createElement("input");r.type="hidden",r.name="surat_id",r.value=$,t.appendChild(r),e.forEach(i=>{const s=document.createElement("input");s.type="hidden",s.name="paths[]",s.value=i,t.appendChild(s)}),document.body.appendChild(t),t.submit(),setTimeout(()=>t.remove(),2e3)}async function X(e){var t;try{$=e;const r=await(await v(`/surat/${e}`)).json();if(!(r!=null&&r.success))return u("Gagal memuat detail surat","error");const n=r.surat;o("#view_no_surat").textContent=n.no_surat||"-",o("#view_kode").textContent=n.kode_keterangan?`${n.kode_surat} - ${n.kode_keterangan}`:n.kode_surat||"-";const i=o("#view_jenis");i&&(i.innerHTML=n.jenis_surat==="Masuk"?`<span class="badge bg-info">
                    <i class="bi bi-box-arrow-in-down me-1"></i>
                    Masuk
               </span>`:`<span class="badge bg-success">
                    <i class="bi bi-box-arrow-up-right me-1"></i>
                    Keluar
               </span>`),o("#view_tanggal").textContent=n.tanggal_surat||"-",o("#view_instansi").textContent=n.instansi||"-",o("#view_pengirim").textContent=n.pengirim||"-",o("#view_penerima").textContent=n.penerima||"-",o("#view_perihal").textContent=n.perihal||"-",o("#view_keterangan").textContent=n.keterangan||"-";const s=o("#view_file");o("#view_created_by").textContent=n.created_by||"-",o("#view_created_at").textContent=n.created_at||"-",o("#view_updated_by").textContent=n.updated_by||"-",o("#view_updated_at").textContent=n.updated_at||"-";const l=o("#view_status");l&&(n.status==="Pernah Diubah"?l.innerHTML=`
            <span class="badge bg-warning text-dark">
                <i class="bi bi-pencil-square me-1"></i>
                Pernah Diubah
            </span>
        `:l.innerHTML=`
            <span class="badge bg-success">
                <i class="bi bi-check-circle me-1"></i>
                Belum Pernah Diubah
            </span>
        `),s&&(s.innerHTML="",Array.isArray(n.files)&&n.files.length?n.files.forEach(d=>{const c=document.createElement("div");c.className="list-group-item d-flex justify-content-between align-items-center",c.innerHTML=`
                            <div class="d-flex align-items-center">
                                <input type="checkbox"
                                       class="form-check-input me-2 file-select-check"
                                       data-path="${d.path}"
                                       data-url="${d.url}">
                                <i class="${H(d.name)} me-2 fs-5"></i>
                                <span class="text-break">${d.name}</span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button"
                                        class="btn btn-outline-primary btn-preview-file"
                                        data-url="${d.url}"
                                        title="Pratinjau">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="${d.url}"
                                   class="btn btn-outline-success btn-direct-download"
                                   data-filename="${d.name}"
                                   title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        `,s.appendChild(c)}):s.innerHTML=`
                        <div class="list-group-item text-muted small">
                            Belum ada file terlampir.
                        </div>`),G(),(t=T("#viewSuratModal"))==null||t.show()}catch(a){console.error(a),u("Gagal menampilkan surat","error")}}function ne(e){var l;if(!e)return;const t=o("#filePreviewBody"),a=o("#file-loading-spinner"),r=o("#previewDownloadLink");if(a&&(a.style.display="flex"),t&&(t.innerHTML=""),r){r.href=e;const d=I(e,"lampiran");r.setAttribute("download",d);const c=o("#preview_filename_label");c&&(c.textContent=d)}const n=e.split(".").pop().toLowerCase();let i;const s=()=>{a&&(a.style.display="none")};["pdf"].includes(n)?(i=document.createElement("iframe"),i.className="w-100",i.style.minHeight="80vh",i.onload=s,i.onerror=s,i.src=e):["jpg","jpeg","png"].includes(n)?(i=document.createElement("img"),i.className="img-fluid",i.onload=s,i.onerror=s,i.src=e):(i=document.createElement("div"),i.className="p-3",i.innerHTML=`
                <p class="mb-1">Pratinjau langsung tidak tersedia.</p>
                <a href="${e}" target="_blank">Klik di sini untuk membuka / mengunduh file.</a>
            `,s()),t&&t.appendChild(i),(l=T("#filePreviewModal"))==null||l.show()}function re(){var e,t,a,r;(e=o("#viewSuratModal"))==null||e.addEventListener("change",n=>{n.target.closest(".file-select-check")&&G()}),document.addEventListener("click",n=>{const i=n.target.closest(".btn-preview-file");if(!i)return;const s=i.dataset.url;s&&ne(s)}),document.addEventListener("click",n=>{const i=n.target.closest(".btn-direct-download");if(!i)return;n.preventDefault();const s=i.getAttribute("href");if(!s){u("URL file tidak ditemukan.","error");return}let l=i.dataset.filename||"lampiran";const d=document.createElement("a");d.href=s,d.download=l,document.body.appendChild(d),d.click(),d.remove()}),(t=o("#btnPrintSelected"))==null||t.addEventListener("click",n=>{n.preventDefault();const i=x("#view_file .file-select-check:checked");if(i.length!==1){u("Pilih tepat satu file yang akan diprint.","warning");return}const s=i[0].dataset.url;if(!s)return;const l=window.open(s,"_blank");l&&l.addEventListener("load",()=>{try{l.print()}catch{}})}),(a=o("#btnDownloadSelected"))==null||a.addEventListener("click",n=>{n.preventDefault();const i=x("#view_file .file-select-check:checked");if(!i.length){u("Pilih minimal satu file lampiran.","warning");return}if(i.length===1){const l=i[0],d=l.dataset.url;if(!d){u("URL file tidak ditemukan.","error");return}let c="lampiran";const p=l.closest(".list-group-item"),h=p==null?void 0:p.querySelector(".text-break");h&&h.textContent.trim()?c=h.textContent.trim():c=I(d,c);const b=document.createElement("a");b.href=d,b.download=c,document.body.appendChild(b),b.click(),b.remove();return}const s=i.map(l=>l.dataset.path).filter(l=>typeof l=="string"&&l.length>0);U(s)}),(r=o("#btnDownloadAll"))==null||r.addEventListener("click",n=>{n.preventDefault();const i=x("#view_file .file-select-check");if(!i.length){u("Tidak ada file lampiran.","warning");return}const s=i.map(l=>l.dataset.path).filter(l=>typeof l=="string"&&l.length>0);U(s)})}const ie=/[^A-Za-z0-9 ]+/g,se=/[^A-Za-z0-9 .\/-]+/g,oe=new Set(["no_surat","kode_surat"]),le=new Set(["perihal","instansi","pengirim","penerima"]);function de(e){if(!e||e.tagName!=="INPUT"&&e.tagName!=="TEXTAREA")return;const t=e.name;let a=null;if(oe.has(t)?a=se:le.has(t)&&(a=ie),!a)return;const r=e.value,n=r.replace(a,"");if(r===n)return;const i=e.selectionStart??n.length,s=r.length-n.length;e.value=n;const l=Math.max(0,i-s);try{e.setSelectionRange(l,l)}catch{}}function ce(){["#addSuratForm","#editSuratForm"].forEach(e=>{const t=o(e);t&&t.addEventListener("input",a=>de(a.target))})}document.addEventListener("DOMContentLoaded",()=>{["resetJenis","resetSort"].forEach(e=>{var t;(t=document.getElementById(e))==null||t.addEventListener("click",()=>{if(k={bulan:null,tahun:null,jenis_surat:null},e==="resetJenis"){const a=document.getElementById("jenis");a&&(a.value="")}g()})})}),document.addEventListener("DOMContentLoaded",()=>{W(),g(),N(),Q(),ee(),te(),ae(),re(),ce(),D("#edit_tanggal"),window.SuratApp={loadTable:g,loadSuratTable:g,viewSurat:X,editSurat:K}})})();
