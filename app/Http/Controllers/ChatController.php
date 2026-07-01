<?php

namespace App\Http\Controllers;

use App\Models\Kode;
use App\Models\PesertaDidik;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private string $model  = 'llama-3.3-70b-versatile';

    /* ============================================================
     * ENTRY POINT — POST /chat/ask
     * ============================================================ */
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'array|max:20',
            'page'    => 'nullable|string|max:200',
        ]);

        $user    = Auth::user();
        $message = trim($request->message);
        $page    = $request->get('page', '/dashboard');
        $history = array_slice($request->input('history', []), -6);

        try {
            $contextData = $this->gatherContext($message);
            $systemPrompt = $this->buildSystemPrompt($user, $page, $contextData);

            $messages = [['role' => 'system', 'content' => $systemPrompt]];
            foreach ($history as $h) {
                if (!empty($h['role']) && !empty($h['content'])) {
                    $messages[] = [
                        'role'    => in_array($h['role'], ['user', 'assistant']) ? $h['role'] : 'user',
                        'content' => mb_substr((string)$h['content'], 0, 1000),
                    ];
                }
            }
            $messages[] = ['role' => 'user', 'content' => $message];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.groq.key'),
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'max_tokens'  => 1024,
                    'temperature' => 0.5,
                    'messages'    => $messages,
                ]);

            if (!$response->successful()) {
                Log::error('Groq API error: ' . $response->status() . ' ' . $response->body());
                throw new \Exception('AI tidak merespons. Status: ' . $response->status());
            }

            $reply = $response->json('choices.0.message.content') ?? 'Maaf, tidak ada respons.';

            $menuForSuggestions = $contextData['navigate'] ?? $contextData['panduan'] ?? null;
            $suggestions = $menuForSuggestions ? $this->buildQuickSuggestions($menuForSuggestions) : [];

            return response()->json(['success' => true, 'message' => $reply, 'suggestions' => $suggestions]);
        } catch (\Throwable $e) {
            Log::error('ChatController ask Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ============================================================
     * UPLOAD FILE — POST /chat/upload
     * ============================================================ */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'files'   => 'required|array|max:3',
            'files.*' => 'file|max:10240',
            'message' => 'nullable|string|max:1000',
        ]);

        $user    = Auth::user();
        $caption = trim($request->input('message', 'Tolong analisis file ini dan berikan ringkasan.'));

        try {
            $textParts = [];

            foreach ($request->file('files') as $file) {
                $ext      = strtolower($file->getClientOriginalExtension());
                $origName = $file->getClientOriginalName();
                $path     = $file->getRealPath();

                if ($ext === 'pdf') {
                    $text = trim(shell_exec("pdftotext " . escapeshellarg($path) . " - 2>/dev/null") ?? '');
                    $textParts[] = "=== File: {$origName} ===\n" . mb_substr($text ?: '[PDF tidak bisa dibaca]', 0, 4000);
                    continue;
                }

                if (in_array($ext, ['docx', 'doc'])) {
                    $tmpDir = sys_get_temp_dir() . '/docx_' . uniqid();
                    mkdir($tmpDir);
                    shell_exec("unzip -q " . escapeshellarg($path) . " word/document.xml -d " . escapeshellarg($tmpDir) . " 2>/dev/null");
                    $xmlFile = $tmpDir . '/word/document.xml';
                    $text = '';
                    if (file_exists($xmlFile)) {
                        $xml  = file_get_contents($xmlFile);
                        $text = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['</w:p>', '</w:tr>'], "\n", $xml)))), 0, 4000);
                    }
                    shell_exec("rm -rf " . escapeshellarg($tmpDir));
                    $textParts[] = "=== File: {$origName} ===\n" . ($text ?: '[Gagal membaca dokumen Word]');
                    continue;
                }

                if (in_array($ext, ['xlsx', 'xls'])) {
                    $tmpDir = sys_get_temp_dir() . '/xlsx_' . uniqid();
                    mkdir($tmpDir);
                    shell_exec("unzip -q " . escapeshellarg($path) . " xl/sharedStrings.xml -d " . escapeshellarg($tmpDir) . " 2>/dev/null");
                    $ssFile = $tmpDir . '/xl/sharedStrings.xml';
                    $text   = file_exists($ssFile) ? mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags(file_get_contents($ssFile)))), 0, 3000) : '';
                    shell_exec("rm -rf " . escapeshellarg($tmpDir));
                    $textParts[] = "=== File: {$origName} ===\n" . ($text ?: '[Gagal membaca file Excel]');
                    continue;
                }

                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $textParts[] = "=== File: {$origName} ===\n[File gambar — tidak bisa dibaca sebagai teks]";
                    continue;
                }

                $textParts[] = "=== File: {$origName} ===\n[Tipe file tidak didukung: .{$ext}]";
            }

            $name = $user->name ?? 'Pengguna';
            $role = optional($user->role)->name ?? 'Staf';

            $response = Http::timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.groq.key'),
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'max_tokens'  => 1024,
                    'temperature' => 0.4,
                    'messages'    => [
                        ['role' => 'system', 'content' => "Kamu adalah asisten AI bernama \"Arsy\" untuk sistem E-Arsip SMA Babussalam. Pengguna: {$name} (Role: {$role}). Analisis file yang dikirim dan berikan ringkasan informatif dalam Bahasa Indonesia. Gunakan format Markdown, JANGAN output HTML tag."],
                        ['role' => 'user',   'content' => $caption . "\n\n" . implode("\n\n", $textParts)],
                    ],
                ]);

            if (!$response->successful()) {
                throw new \Exception('Groq error: ' . $response->status());
            }

            $reply = $response->json('choices.0.message.content') ?? 'Maaf, tidak bisa menganalisis file.';
            return response()->json(['success' => true, 'message' => $reply, 'mode' => 'file']);
        } catch (\Throwable $e) {
            Log::error('ChatController uploadFile Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menganalisis file: ' . $e->getMessage()], 500);
        }
    }

    /* ============================================================
     * QUICK SUGGESTIONS — saran tanya cepat setelah navigasi menu
     * ============================================================ */
    private function buildQuickSuggestions(string $menu): array
    {
        $labels = ['peserta_didik' => 'PesertaDidik', 'surat' => 'Surat', 'kode' => 'Kode Surat', 'laporan' => 'Laporan', 'user' => 'User'];

        $khusus = [
            'peserta_didik' => [
                ['label' => '➕ Cara tambah peserta_didik', 'msg' => 'bagaimana cara tambah peserta_didik baru'],
                ['label' => '📂 Dokumen belum lengkap', 'msg' => 'tampilkan peserta_didik yang belum lengkap dokumen'],
                ['label' => '✏️ Cara edit/upload dokumen', 'msg' => 'bagaimana cara edit dan upload ulang dokumen peserta_didik'],
            ],
            'surat' => [
                ['label' => '➕ Cara tambah surat', 'msg' => 'bagaimana cara tambah surat'],
                ['label' => '🔢 Aturan kode surat', 'msg' => 'apa aturan kode surat untuk surat masuk dan keluar'],
                ['label' => '📎 Cara upload lampiran', 'msg' => 'bagaimana cara upload lampiran surat'],
            ],
            'kode' => [
                ['label' => '➕ Cara tambah kode', 'msg' => 'bagaimana cara tambah kode surat baru'],
                ['label' => '✏️ Cara edit kode', 'msg' => 'bagaimana cara edit kode surat'],
                ['label' => '🔍 Cari kode tertentu', 'msg' => 'cari kode surat undangan'],
            ],
            'laporan' => [
                ['label' => '📊 Rekap bulan ini', 'msg' => 'tampilkan rekap surat bulan ini'],
                ['label' => '📅 Rekap tahunan', 'msg' => 'tampilkan rekap surat tahun ini'],
                ['label' => '📥 Cara export laporan', 'msg' => 'bagaimana cara export laporan ke pdf dan excel'],
            ],
            'user' => [
                ['label' => '➕ Cara tambah user', 'msg' => 'bagaimana cara tambah user baru'],
                ['label' => '✏️ Cara edit user', 'msg' => 'bagaimana cara edit data user'],
                ['label' => '🗑 Cara hapus user', 'msg' => 'bagaimana cara hapus user'],
            ],
        ];

        $suggestions = $khusus[$menu] ?? [];

        // Tambahkan saran navigasi ke menu lain (selain menu yang sedang aktif)
        foreach ($labels as $key => $label) {
            if ($key === $menu) continue;
            $suggestions[] = ['label' => "🧭 Buka menu {$label}", 'msg' => "buka menu {$key}"];
        }

        return array_slice($suggestions, 0, 7);
    }

    /* ============================================================
     * PANDUAN DETAIL — langkah-langkah lengkap per menu
     * ============================================================ */
    private function panduanDetail(string $menu): string
    {
        $panduan = [
            'peserta_didik' => "Cara pakai Menu PesertaDidik:\n"
                . "1. **Tambah peserta_didik** — klik tombol \"Tambah PesertaDidik\", isi data diri (nama, jenis kelamin, tahun angkatan, tempat/tanggal lahir, rombel, alamat), centang dokumen yang ingin diunggah (PPDB, KK, Akte, KTP Ortu, KTS, Foto, Ijazah SMP/SMA), lalu klik Simpan.\n"
                . "2. **Edit / upload ulang dokumen** — klik tombol Edit pada baris peserta_didik, ubah data atau centang kotak dokumen lalu pilih file baru untuk mengganti dokumen lama, lalu Simpan.\n"
                . "3. **Lihat / unduh dokumen** — klik tombol Detail/Lihat pada baris peserta_didik untuk melihat semua dokumen yang sudah diunggah, atau unduh semuanya sekaligus.\n"
                . "4. **Hapus peserta_didik** — klik tombol Hapus pada baris peserta_didik, lalu konfirmasi.\n"
                . "5. **Filter & cari** — gunakan kolom pencarian atau filter Rombel (A/B), Tahun Angkatan, dan Status Kelengkapan dokumen (lengkap/belum lengkap) di bagian atas tabel.",
            'surat' => "Cara pakai Menu Surat:\n"
                . "1. **Tambah surat** — klik tombol \"Tambah Surat\", pilih Jenis Surat dulu:\n"
                . "   - Jika **Masuk** → kolom Kode Surat berupa isian teks, ketik kode secara manual (opsional).\n"
                . "   - Jika **Keluar** → kolom Kode Surat otomatis berubah jadi dropdown, pilih kodenya dari daftar master Kode Surat (wajib).\n"
                . "   Lalu isi No. Surat, Tanggal, Perihal, Instansi, Pengirim/Penerima, dan lampirkan file (gambar/PDF/Word/Excel/PPT, bisa lebih dari satu), lalu Simpan.\n"
                . "2. **Edit / upload ulang lampiran** — klik tombol Edit pada baris surat; kolom Kode Surat akan ikut menyesuaikan (teks manual untuk Masuk, dropdown untuk Keluar) sesuai Jenis Surat yang dipilih. Ubah data atau tambahkan/ganti file lampiran, lalu Simpan.\n"
                . "3. **Lihat / unduh lampiran** — klik tombol Detail/Lihat untuk membuka atau mengunduh file surat yang sudah diunggah.\n"
                . "4. **Hapus surat** — klik tombol Hapus pada baris surat, lalu konfirmasi.\n"
                . "5. **Filter & cari** — gunakan kolom pencarian atau filter Jenis (Masuk/Keluar), rentang tanggal, dan Kode Surat di bagian atas tabel.",
            'kode' => "Cara pakai Menu Kode Surat:\n"
                . "1. **Tambah kode** — klik tombol \"Tambah Kode\", isi Kode klasifikasi dan Deskripsinya, lalu Simpan.\n"
                . "2. **Edit kode** — klik tombol Edit pada baris kode; jika kode diganti, semua surat yang memakai kode lama otomatis ikut diperbarui ke kode baru.\n"
                . "3. **Hapus kode** — klik tombol Hapus pada baris kode.\n"
                . "4. Catatan: kode surat juga bisa diketik manual langsung saat menambah/mengedit surat masuk (tidak harus dari daftar master ini) — Arsy tetap bisa mencarikannya jika ditanya.",
            'laporan' => "Cara pakai Menu Laporan:\n"
                . "1. Pilih periode (bulan & tahun) dan jenis surat (Masuk/Keluar/Semua) pada filter di atas.\n"
                . "2. Klik \"Tampilkan\" untuk melihat rekap.\n"
                . "3. Gunakan tombol Cetak, Export PDF, Export Excel, atau Export Word untuk mengunduh laporan.",
            'user' => "Cara pakai Menu User:\n"
                . "1. **Tambah user** — klik tombol \"Tambah User\", isi nama, email, password, dan role, lalu Simpan.\n"
                . "2. **Edit user** — klik tombol Edit pada baris user untuk mengubah data atau foto profil.\n"
                . "3. **Hapus user** — klik tombol Hapus pada baris user, lalu konfirmasi.",
        ];

        return $panduan[$menu] ?? '';
    }

    /* ============================================================
     * GATHER CONTEXT — ambil data DB yang relevan
     * ============================================================ */
    private function gatherContext(string $message): array
    {
        $msg     = mb_strtolower($message);
        $context = [];

        // Navigasi menu
        $menuMap = [
            'peserta_didik'   => ['menu peserta_didik', 'halaman peserta_didik', 'buka peserta_didik', 'menuju peserta_didik', 'ke peserta_didik', 'pergi peserta_didik', 'buka menu peserta_didik'],
            'surat'   => ['menu surat', 'halaman surat', 'buka surat', 'menuju surat', 'ke surat'],
            'kode'    => ['menu kode', 'halaman kode', 'buka kode', 'menuju kode'],
            'laporan' => ['menu laporan', 'halaman laporan', 'buka laporan', 'menuju laporan'],
            'user'    => ['menu user', 'halaman user', 'buka user', 'kelola user', 'menuju user'],
        ];
        foreach ($menuMap as $route => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($msg, $kw)) {
                    $context['navigate'] = $route;
                    return $context;
                }
            }
        }

        // Pertanyaan "cara pakai" / panduan langkah-langkah suatu menu
        // (mis. "bagaimana cara tambah peserta_didik", "cara upload lampiran surat")
        if (preg_match('/\baturan\b.*\bkode\b/u', $msg) || preg_match('/\bkode\b.*\b(masuk|keluar)\b/u', $msg)) {
            $context['panduan'] = 'surat';
            return $context;
        }
        $menuNamesPanduan = ['peserta_didik' => 'peserta_didik', 'kode' => 'kode', 'surat' => 'surat', 'laporan' => 'laporan', 'user' => 'user'];
        $actionWords = ['cara', 'bagaimana', 'gimana', 'tutorial', 'panduan', 'langkah', 'tambah', 'edit', 'upload', 'unggah', 'hapus', 'kelola', 'ubah', 'aturan'];
        foreach ($menuNamesPanduan as $menuKey => $menuWord) {
            if (str_contains($msg, $menuWord)) {
                foreach ($actionWords as $a) {
                    if (str_contains($msg, $a)) {
                        $context['panduan'] = $menuKey;
                        return $context;
                    }
                }
            }
        }

        // Statistik — hanya jika kata kunci spesifik & tidak sedang mencari data
        $hasDataKeyword = preg_match('/\bsurat\b/', $msg) || preg_match('/\bpeserta_didik\b/', $msg) || preg_match('/\bpeserta didik\b/', $msg);
        if (!$hasDataKeyword && preg_match('/\b(berapa|total|jumlah|statistik|rekap)\b/', $msg)) {
            $context['stats'] = $this->queryStats();
        }

        // Data surat — hanya jika ada keyword pencarian spesifik (bukan sekadar kata "surat")
        $suratSearchKeywords = ['surat masuk', 'surat keluar', 'nomor', 'no.', 'nomer', 'perihal', 'dari', 'instansi',
            'wisuda', 'undangan', 'izin', 'keputusan', 'edaran', 'pemberitahuan', 'permohonan', 'pengantar',
            'rapat', 'pengumuman', 'tugas', 'rekomendasi', 'dispensasi', 'ujian', 'proposal',
            'bulan ini', 'bulan lalu', 'tahun ini', 'januari', 'februari', 'maret', 'april', 'mei', 'juni',
            'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
        $suratSearchActive = false;
        foreach ($suratSearchKeywords as $kw) {
            if (str_contains($msg, $kw)) { $suratSearchActive = true; break; }
        }
        if ($suratSearchActive) {
            [$suratData, $suratMeta, $suratUrlSemua] = $this->querySurat($message);
            $context['surat'] = $suratData;
            $context['surat_meta'] = $suratMeta;
            $context['surat_url_semua'] = $suratUrlSemua;
        }

        // Data peserta didik — hanya jika ada nama/filter spesifik
        $pdSearchKeywords = ['peserta_didik', 'peserta didik', 'bernama', 'cari', 'rombel', 'angkatan', 'belum lengkap', 'dokumen'];
        $pdSearchActive = false;
        foreach ($pdSearchKeywords as $kw) {
            if (str_contains($msg, $kw)) { $pdSearchActive = true; break; }
        }
        if ($pdSearchActive) {
            $pesertaDidikData = $this->queryPesertaDidik($message);
            if (!empty($pesertaDidikData)) $context['peserta_didik'] = $pesertaDidikData;
        }

        // Kode surat
        if (preg_match('/\bkode\b/', $msg)) {
            $kodeData = $this->queryKode($message);
            if (!empty($kodeData)) $context['kode'] = $kodeData;
        }

        return $context;
    }

    /* ============================================================
     * BUILD SYSTEM PROMPT
     * ============================================================ */
    private function buildSystemPrompt($user, string $page, array $context): string
    {
        $name = $user->name ?? 'Pengguna';
        $role = optional($user->role)->name ?? 'Staf';

        $contextSection = '';

        if (!empty($context['navigate'])) {
            $routes = ['peserta_didik' => '/peserta-didik', 'surat' => '/surat', 'kode' => '/kode', 'laporan' => '/laporan', 'user' => '/user'];
            $url    = $routes[$context['navigate']] ?? '/' . $context['navigate'];
            $label  = ucfirst($context['navigate']);

            $fungsi = [
                'peserta_didik'   => 'Menu PesertaDidik digunakan untuk menyimpan data diri peserta_didik beserta dokumen pendukungnya (PPDB, KK, Akte, KTP Orang Tua, KTS, Foto, Ijazah SMP/SMA) dan memantau status kelengkapan dokumen tiap peserta_didik.',
                'surat'   => 'Menu Surat digunakan untuk mencatat dan mengarsipkan surat masuk maupun surat keluar beserta lampirannya, termasuk nomor surat, kode klasifikasi, perihal, instansi, dan pengirim/penerima.',
                'kode'    => 'Menu Kode Surat digunakan untuk mengelola daftar master kode klasifikasi surat, yang nantinya dipilih saat menambahkan surat keluar.',
                'laporan' => 'Menu Laporan digunakan untuk melihat rekap jumlah surat masuk dan keluar per bulan/tahun, serta mengekspor laporan ke PDF, Excel, atau Word.',
                'user'    => 'Menu User digunakan untuk mengelola akun pengguna sistem E-Arsip, termasuk menambah, mengedit, dan menghapus user.',
            ];
            $deskripsi = $fungsi[$context['navigate']] ?? '';

            $contextSection = "\n\n## AKSI NAVIGASI\nPengguna ingin pergi ke menu {$label}. Arahkan dengan link Markdown: [Buka Menu {$label}]({$url}), lalu jelaskan SINGKAT (1-2 kalimat saja) pengertian/fungsi menu ini — JANGAN jelaskan langkah-langkah cara tambah/edit/upload/hapus secara rinci di sini, karena itu akan ditanyakan lewat tombol saran cepat selanjutnya. Gunakan deskripsi berikut sebagai acuan:\n\n{$deskripsi}";
        }
        if (!empty($context['panduan'])) {
            $label  = ucfirst($context['panduan']);
            $langkah = $this->panduanDetail($context['panduan']);
            $contextSection .= "\n\n## PANDUAN PENGGUNAAN MENU {$label}\nPengguna menanyakan cara penggunaan menu {$label}. Jelaskan langkah-langkahnya secara jelas dan ringkas (gunakan format list) berdasarkan panduan berikut:\n\n{$langkah}";
        }
        if (!empty($context['stats'])) {
            $contextSection .= "\n\n## DATA STATISTIK ARSIP\n" . json_encode($context['stats'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        if (isset($context['surat'])) {
            $count = count($context['surat']);
            $meta  = $context['surat_meta'] ?? '';
            $urlSemua = $context['surat_url_semua'] ?? '';
            if ($count > 0) {
                $linkSemua = $urlSemua ? "\n\n🔗 LINK WAJIB DITAMPILKAN DI PALING ATAS: [📋 Lihat Semua Surat ({$count} ditampilkan)]({$urlSemua}) — tampilkan link ini sebagai baris pertama sebelum daftar surat." : '';
                $contextSection .= "\n\n## DATA SURAT ({$count} hasil dari database){$meta}{$linkSemua}\n" . json_encode($context['surat'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $contextSection .= "\n\n⚠️ WAJIB: (1) Tampilkan link [📋 Lihat Semua Surat]({$urlSemua}) di baris PERTAMA. (2) Tampilkan HANYA data surat di atas. (3) JANGAN tambahkan link per item surat.";
            } else {
                $contextSection .= "\n\n## DATA SURAT (0 hasil dari database){$meta}\n\n⚠️ WAJIB: Database tidak menemukan surat sesuai filter tersebut. Sampaikan kepada pengguna bahwa tidak ada surat yang cocok dengan kriteria itu. JANGAN mengarang atau menyebutkan contoh surat apapun.";
            }
        }
        if (!empty($context['peserta_didik'])) {
            $count = count($context['peserta_didik']);
            $contextSection .= "\n\n## DATA PESERTA_DIDIK ({$count} hasil dari database)\n" . json_encode($context['peserta_didik'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        if (!empty($context['kode'])) {
            $count = count($context['kode']);
            $contextSection .= "\n\n## DATA KODE SURAT ({$count} hasil dari database)\n" . json_encode($context['kode'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return <<<PROMPT
Kamu adalah asisten AI bernama "Arsy" untuk sistem E-Arsip SMA Babussalam.

## IDENTITAS & KEPRIBADIAN
- Nama: Arsy
- Sekolah: SMA Babussalam
- Pengguna: {$name} (Role: {$role})
- Halaman aktif: {$page}
- Kepribadian: ramah, cepat tanggap, komunikatif, dan proaktif. Gunakan emoji secukupnya agar terasa hidup.

## CARA BERKOMUNIKASI
- Sapa pengguna dengan nama mereka secara natural jika relevan
- Jawab langsung ke inti pertanyaan, tidak bertele-tele
- Gunakan bahasa Indonesia yang santai tapi tetap profesional
- Jika ada data, langsung tampilkan — jangan tanya "apakah kamu ingin melihat data?"
- Selalu tawarkan tindak lanjut di akhir jawaban (contoh: "Mau saya carikan yang lain?", "Ada hal lain yang bisa saya bantu?")
- Jika pertanyaan ambigu, tebak intent yang paling mungkin dan langsung jawab, bukan meminta klarifikasi

## KEMAMPUAN LENGKAP
1. **Navigasi** — langsung arahkan ke menu yang diminta dengan link
2. **Cari surat** — filter berdasarkan tanggal, jenis, perihal, instansi, nomor
3. **Cari peserta_didik** — filter berdasarkan nama, rombel, angkatan, status dokumen
4. **Statistik** — sajikan angka dengan konteks yang bermakna
5. **Panduan** — jelaskan cara pakai fitur dengan langkah yang jelas
6. **Kode surat** — tampilkan daftar kode klasifikasi
7. **Analisis file** — ringkas isi dokumen yang dilampirkan

## MODUL APLIKASI
- **Surat** → /surat — surat masuk & keluar, upload lampiran, edit, hapus
- **PesertaDidik** → /peserta-didik — data & dokumen peserta_didik, status kelengkapan
- **Kode Surat** → /kode — master kode klasifikasi
- **Laporan** → /laporan — rekap & export PDF/Excel/Word
- **User** → /user — kelola akun pengguna

## ATURAN OUTPUT
1. SEMUA pengguna bisa tambah/edit/hapus data — JANGAN sebut "hubungi admin"
2. Link navigasi pakai format Markdown: [Nama Menu](url)
3. Output HANYA Markdown — JANGAN output tag HTML, onclick, style attribute
4. Jika ada data DB di bawah, GUNAKAN data itu — jangan bilang "saya tidak punya akses data"
5. **KRITIS — JANGAN MENGARANG DATA SURAT**: Jika ada section "DATA SURAT" di bawah, tampilkan HANYA surat yang ada di sana. Jika datanya kosong (0 hasil), sampaikan bahwa tidak ada surat yang cocok dengan filter tersebut — JANGAN menyebutkan contoh surat fiktif apapun.
6. **PENTING — TANGGAL SURAT vs TANGGAL INPUT**: Filter "bulan ini", "bulan lalu", dll mengacu pada **tanggal surat** (tanggal tertulis di surat), BUKAN tanggal surat diinput ke sistem. Jika hasil pencarian kosong, jelaskan hal ini kepada pengguna — surat mungkin sudah diinput tapi tanggal suratnya berbeda dari bulan yang dicari.
7. **FORMAT TAMPILAN DATA SURAT**: Jika ada data surat dan ada URL_SEMUA di meta, tampilkan link "[📋 Lihat Semua Surat di Halaman Surat](URL_SEMUA)" di PALING ATAS sebelum daftar surat. Setiap item surat cukup tampilkan: nomor, jenis, tanggal, perihal, instansi — TANPA link per item.
8. Aturan Kode Surat (PENTING, jangan sampai salah jawab): saat menambah/mengedit surat, jika Jenis Surat = **Masuk**, kolom Kode Surat berupa isian teks bebas (diketik manual, opsional). Jika Jenis Surat = **Keluar**, kolom Kode Surat berubah jadi dropdown dan WAJIB dipilih dari daftar master Kode Surat — tidak bisa diketik manual.
9. **BATASAN KONTEKS (SANGAT KRITIS)**: Kamu HANYA BOLEH menjawab pertanyaan yang berkaitan dengan sistem E-Arsip, navigasi menu, panduan penggunaan aplikasi, atau data yang diberikan di prompt ini. Jika pengguna menanyakan hal UMUM di luar konteks (seperti pertanyaan sejarah, coding, matematika, resep, cuaca, dll), KAMU WAJIB MENOLAKNYA dengan sopan dan menjelaskan bahwa kamu khusus dibuat sebagai asisten E-Arsip.{$contextSection}
PROMPT;
    }

    /* ============================================================
     * QUERY SURAT
     * ============================================================ */
    private function querySurat(string $message): array
    {
        $msg   = mb_strtolower($message);
        $query = Surat::with('kode');
        $filterDesc = [];

        // Filter jenis surat DAHULU
        if (str_contains($msg, 'surat masuk')) {
            $query->where('jenis_surat', 'Masuk');
            $filterDesc[] = 'Jenis: Masuk';
        } elseif (str_contains($msg, 'surat keluar')) {
            $query->where('jenis_surat', 'Keluar');
            $filterDesc[] = 'Jenis: Keluar';
        }

        // Filter waktu (berdasarkan tanggal_surat = tanggal tertulis di surat, bukan tanggal diinput)
        $bulanNama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        if (str_contains($msg, 'bulan ini')) {
            $query->whereYear('tanggal_surat', now()->year)->whereMonth('tanggal_surat', now()->month);
            $filterDesc[] = 'Tanggal surat: ' . $bulanNama[now()->month] . ' ' . now()->year . ' (' . now()->format('m/Y') . ')';
        } elseif (str_contains($msg, 'bulan lalu')) {
            $last = now()->subMonth();
            $query->whereYear('tanggal_surat', $last->year)->whereMonth('tanggal_surat', $last->month);
            $filterDesc[] = 'Tanggal surat: ' . $bulanNama[$last->month] . ' ' . $last->year;
        } elseif (str_contains($msg, 'tahun ini')) {
            $query->whereYear('tanggal_surat', now()->year);
            $filterDesc[] = 'Tahun: ' . now()->year;
        } elseif (preg_match('/\b(20\d{2})\b/', $message, $m)) {
            $query->whereYear('tanggal_surat', (int) $m[1]);
            $filterDesc[] = 'Tahun: ' . $m[1];
        }

        // Filter nama bulan (hanya jika tidak sudah ada filter waktu)
        if (!str_contains($msg, 'bulan ini') && !str_contains($msg, 'bulan lalu') && !str_contains($msg, 'tahun ini')) {
            $bulanMap = ['januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12];
            foreach ($bulanMap as $nama => $num) {
                if (str_contains($msg, $nama)) {
                    $query->whereMonth('tanggal_surat', $num);
                    $filterDesc[] = 'Bulan: ' . ucfirst($nama);
                    break;
                }
            }
        }

        $perihalKw = ['wisuda', 'undangan', 'izin', 'keputusan', 'edaran', 'pemberitahuan', 'permohonan', 'pengantar', 'rapat', 'pengumuman', 'tugas', 'rekomendasi', 'dispensasi', 'beapeserta_didik', 'ujian', 'laporan', 'proposal'];
        foreach ($perihalKw as $kw) {
            if (str_contains($msg, $kw)) {
                $query->where(fn($q) => $q->whereRaw('LOWER(perihal) LIKE ?', ["%{$kw}%"])->orWhereRaw('LOWER(keterangan) LIKE ?', ["%{$kw}%"]));
                $filterDesc[] = 'Perihal: ' . $kw;
                break;
            }
        }

        if (preg_match('/(?:nomor|no\.?|nomer)\s+(?:surat\s+)?([A-Z0-9\/\-\.]+)/i', $message, $m)) {
            $query->where('no_surat', 'LIKE', '%' . trim($m[1]) . '%');
            $filterDesc[] = 'No. Surat: ' . $m[1];
        }

        $meta = !empty($filterDesc) ? ' [Filter: ' . implode(', ', $filterDesc) . ']' : '';

        // Bangun URL filter untuk link "Lihat Semua" berdasarkan filter aktif
        $urlParams = [];
        if (str_contains($msg, 'bulan ini')) {
            $urlParams['tanggal'] = now()->format('Y-m');
        } elseif (str_contains($msg, 'bulan lalu')) {
            $urlParams['tanggal'] = now()->subMonth()->format('Y-m');
        } elseif (str_contains($msg, 'tahun ini')) {
            $urlParams['tanggal'] = now()->format('Y');
        }
        if (str_contains($msg, 'surat masuk')) $urlParams['jenis'] = 'Masuk';
        elseif (str_contains($msg, 'surat keluar')) $urlParams['jenis'] = 'Keluar';

        $urlSemua = '/surat' . (!empty($urlParams) ? '?' . http_build_query($urlParams) : '');
        if (!empty($urlParams)) {
            $meta .= " [URL_SEMUA: {$urlSemua}]";
        }

        $results = $query->orderBy('tanggal_surat', 'desc')->limit(5)->get();

        // Jika hasil kosong & ada filter jenis, cek apakah ada surat jenis lain di periode yang sama
        if ($results->isEmpty() && (str_contains($msg, 'surat masuk') || str_contains($msg, 'surat keluar'))) {
            $jenisLain = str_contains($msg, 'surat masuk') ? 'Keluar' : 'Masuk';
            $qAlt = Surat::query();
            $qAlt->where('jenis_surat', $jenisLain);
            if (str_contains($msg, 'bulan ini')) {
                $qAlt->whereYear('tanggal_surat', now()->year)->whereMonth('tanggal_surat', now()->month);
            } elseif (str_contains($msg, 'bulan lalu')) {
                $last = now()->subMonth();
                $qAlt->whereYear('tanggal_surat', $last->year)->whereMonth('tanggal_surat', $last->month);
            } elseif (str_contains($msg, 'tahun ini')) {
                $qAlt->whereYear('tanggal_surat', now()->year);
            }
            $countAlt = $qAlt->count();
            if ($countAlt > 0) {
                $meta .= " [INFO: tidak ada surat {$filterDesc[0]}, tapi ada {$countAlt} surat {$jenisLain} pada periode yang sama]";
            }
        }

        return [$results->map(fn($s) => [
            'no'      => $s->no_surat,
            'jenis'   => $s->jenis_surat,
            'tgl'     => optional($s->tanggal_surat)->format('d/m/Y'),
            'perihal' => mb_substr($s->perihal, 0, 80),
            'dari/ke' => mb_substr($s->instansi, 0, 60),
        ])->toArray(), $meta, $urlSemua];
    }

    /* ============================================================
     * QUERY PESERTA_DIDIK
     * ============================================================ */
    private function queryPesertaDidik(string $message): array
    {
        $msg   = mb_strtolower($message);
        $query = PesertaDidik::query();

        if (preg_match('/\b(?:bernama|nama)\s+([A-Za-z\s]{2,40})/iu', $message, $m)) {
            $query->whereRaw('LOWER(nama_peserta_didik) LIKE ?', ['%' . mb_strtolower(trim($m[1])) . '%']);
        } elseif (preg_match('/\b(?:cari|carikan|info|detail)\s+(?:peserta_didik\s+)?([A-Za-z]{2,30})/iu', $message, $m)) {
            $bukan = ['peserta_didik', 'surat', 'kode', 'data', 'rombel', 'menu', 'saya', 'semua'];
            if (!in_array(mb_strtolower($m[1]), $bukan)) {
                $query->whereRaw('LOWER(nama_peserta_didik) LIKE ?', ['%' . mb_strtolower($m[1]) . '%']);
            }
        }

        if (preg_match('/\brombel\s*a\b/u', $msg)) $query->where('rombel', 'A');
        elseif (preg_match('/\brombel\s*b\b/u', $msg)) $query->where('rombel', 'B');

        if (preg_match('/(?:angkatan|tahun)\s+(20\d{2})/i', $message, $m)) $query->where('tahun_angkatan', $m[1]);

        if (str_contains($msg, 'belum lengkap')) $query->where('status', 'belum lengkap');
        elseif (str_contains($msg, 'lengkap') && !str_contains($msg, 'belum')) $query->where('status', 'lengkap');

        return $query->orderBy('tahun_angkatan', 'desc')->limit(8)->get()->map(fn($s) => [
            'nama'    => $s->nama_peserta_didik,
            'jk'      => $s->jenis_kelamin === 'L' ? 'L' : 'P',
            'rombel'  => $s->rombel,
            'angkatan'=> $s->tahun_angkatan,
            'status'  => $s->status,
        ])->toArray();
    }

    /* ============================================================
     * QUERY KODE
     * ============================================================ */
    private function queryKode(string $message): array
    {
        $msg  = mb_strtolower($message);
        $stop = ['kode', 'surat', 'daftar', 'list', 'tampilkan', 'cari', 'lihat', 'apa', 'semua'];
        $words = array_filter(explode(' ', $msg), fn($w) => strlen($w) > 2 && !in_array($w, $stop));

        // Hanya ambil dari tabel master kode (lebih ringan, tidak perlu scan tabel surat)
        $query = Kode::query();
        if (!empty($words)) {
            $query->where(function ($q) use ($words) {
                foreach ($words as $k) {
                    $q->orWhere('kode', 'LIKE', "%{$k}%")->orWhere('description', 'LIKE', "%{$k}%");
                }
            });
        }

        return $query->orderBy('kode')->limit(10)->get()->map(fn($k) => [
            'kode' => $k->kode,
            'ket'  => mb_substr($k->description, 0, 80),
        ])->toArray();
    }

    /* ============================================================
     * QUERY STATISTIK
     * ============================================================ */
    private function queryStats(): array
    {
        $tahunIni = now()->year;
        return [
            'surat' => [
                'total'     => Surat::count(),
                'masuk'     => Surat::where('jenis_surat', 'Masuk')->count(),
                'keluar'    => Surat::where('jenis_surat', 'Keluar')->count(),
                'tahun_ini' => Surat::whereYear('tanggal_surat', $tahunIni)->count(),
                'bulan_ini' => Surat::whereYear('tanggal_surat', $tahunIni)->whereMonth('tanggal_surat', now()->month)->count(),
            ],
            'peserta_didik' => [
                'total'         => PesertaDidik::count(),
                'rombel_a'      => PesertaDidik::where('rombel', 'A')->count(),
                'rombel_b'      => PesertaDidik::where('rombel', 'B')->count(),
                'lengkap'       => PesertaDidik::where('status', 'lengkap')->count(),
                'belum_lengkap' => PesertaDidik::where('status', 'belum lengkap')->count(),
            ],
            'kode' => ['total' => Kode::count()],
        ];
    }

    /* ============================================================
     * REKAP BULANAN — GET /chat/rekap
     * ============================================================ */
    public function rekapBulanan(Request $request)
    {
        $request->validate(['bulan' => 'required|numeric|between:1,12', 'tahun' => 'required|numeric|digits:4', 'jenis' => 'nullable|in:masuk,keluar', 'page' => 'nullable|integer|min:1']);

        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;
        $query = \App\Models\Laporan::forMonth($tahun, $bulan)->orderBy('tanggal_surat', 'desc');

        if ($request->jenis && in_array(strtolower($request->jenis), ['masuk', 'keluar'])) {
            $query->where('jenis_surat', ucfirst(strtolower($request->jenis)));
        }

        $paginator = $query->paginate(10)->withQueryString();
        $totals    = \App\Models\Laporan::getMonthlyTotals($tahun, $bulan);

        return response()->json([
            'surat'        => $paginator->items(),
            'total_masuk'  => $totals->total_masuk,
            'total_keluar' => $totals->total_keluar,
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'total'        => $paginator->total(),
        ]);
    }

    /* ============================================================
     * REKAP TAHUNAN — GET /chat/rekap-tahunan
     * ============================================================ */
    public function rekapTahunan(Request $request)
    {
        $request->validate(['tahun' => 'required|numeric|digits:4', 'jenis' => 'nullable|in:masuk,keluar']);

        $tahun    = (int) $request->tahun;
        $months   = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $perMonth = \App\Models\Laporan::aggregatePerMonth($tahun);

        $bulanData = [];
        $totalM = $totalK = 0;
        for ($m = 1; $m <= 12; $m++) {
            $row    = $perMonth->get($m);
            $masuk  = $row ? (int) $row->total_masuk  : 0;
            $keluar = $row ? (int) $row->total_keluar : 0;
            $totalM += $masuk;
            $totalK += $keluar;
            $bulanData[] = ['bulan' => $m, 'bulan_nama' => $months[$m], 'total_masuk' => $masuk, 'total_keluar' => $keluar];
        }

        return response()->json(['bulan_data' => $bulanData, 'total_masuk_all' => $totalM, 'total_keluar_all' => $totalK]);
    }
}

