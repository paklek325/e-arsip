<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;

class LaporanController extends Controller
{
    // ===========================
    // HALAMAN FILTER (AWAL)
    // ===========================

    public function filter(Request $request)
    {
        $availableYears = Laporan::selectRaw('YEAR(tanggal_surat) as tahun')
            ->whereNotNull('tanggal_surat')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        return view('laporan.index', compact('availableYears'));
    }

    // ===========================
    // GENERATE LAPORAN
    // ===========================

    public function generate(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'tipe'  => 'required|in:Tahun,Bulan',
                'tahun' => 'required|numeric|digits:4',
                'bulan' => 'nullable|numeric|between:1,12',
                'jenis' => 'nullable|in:masuk,keluar',
            ]);
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Data yang diberikan tidak valid.',
                    'errors'  => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $tipe_rekap  = $validatedData['tipe'];
        $tahun       = (int) $validatedData['tahun'];
        $bulan       = isset($validatedData['bulan']) ? (int) $validatedData['bulan'] : null;
        $filterJenis = $validatedData['jenis'] ?? null;

        $months = [
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];
        $bulan_nama = $bulan ? ($months[$bulan] ?? null) : null;

        $totalSuratMasukBulan  = 0;
        $totalSuratKeluarBulan = 0;
        $surat                 = null;
        $data_laporan          = collect(); // selalu ada agar blade tidak error

        // ------------------------------------------------------------------
        // 1) REKAP TAHUNAN  (tipe=Tahun  ATAU  tipe=Bulan tanpa bulan)
        // ------------------------------------------------------------------
        if ($tipe_rekap === 'Tahun' || ($tipe_rekap === 'Bulan' && !$bulan)) {

            $perMonth = Laporan::aggregatePerMonth($tahun);

            for ($m = 1; $m <= 12; $m++) {
                $row = $perMonth->get($m);
                $data_laporan->push((object) [
                    'bulan'        => $m,
                    'bulan_nama'   => $months[$m],
                    'total_masuk'  => $row ? (int) $row->total_masuk  : 0,
                    'total_keluar' => $row ? (int) $row->total_keluar : 0,
                ]);
            }

            $view_data = compact(
                'data_laporan',
                'tipe_rekap',
                'tahun',
                'bulan',
                'bulan_nama',
                'filterJenis',
                'totalSuratMasukBulan',
                'totalSuratKeluarBulan',
                'surat'
            );

            if ($request->ajax()) {
                return view('laporan.partials.hasil', $view_data);
            }

            return view('laporan.index', $view_data);
        }

        // ------------------------------------------------------------------
        // 2) DETAIL BULANAN  (tipe=Bulan DAN bulan tersedia)
        // ------------------------------------------------------------------
        if ($tipe_rekap === 'Bulan' && $bulan) {

            $perPage = 10;

            $surat = Laporan::getSuratForMonth($tahun, $bulan, $perPage, $filterJenis);

            if ($filterJenis) {
                $totalFiltered = method_exists($surat, 'total')
                    ? $surat->total()
                    : $surat->count();

                if ($filterJenis === 'masuk') {
                    $totalSuratMasukBulan  = $totalFiltered;
                    $totalSuratKeluarBulan = 0;
                } else {
                    $totalSuratMasukBulan  = 0;
                    $totalSuratKeluarBulan = $totalFiltered;
                }
            } else {
                $totals = Laporan::getMonthlyTotals($tahun, $bulan);
                $totalSuratMasukBulan  = $totals->total_masuk  ?? 0;
                $totalSuratKeluarBulan = $totals->total_keluar ?? 0;
            }

            $view_data = compact(
                'surat',
                'data_laporan',          // collect() kosong — blade butuh ini agar tidak error
                'tipe_rekap',
                'tahun',
                'bulan',
                'bulan_nama',
                'totalSuratMasukBulan',
                'totalSuratKeluarBulan',
                'filterJenis'
            );

            if ($request->ajax()) {
                return view('laporan.partials.hasil', $view_data);
            }

            return view('laporan.index', $view_data);
        }

        return back()->with('error', 'Tipe laporan tidak valid.');
    }

    // ===========================
    // EXPORT: PDF / EXCEL / WORD
    // ===========================

    public function export(Request $request)
    {
        $format = $request->get('format', 'pdf');

        $validated = $request->validate([
            'tipe'  => 'required|in:Tahun,Bulan',
            'tahun' => 'required|numeric|digits:4',
            'bulan' => 'nullable|numeric|between:1,12',
            'jenis' => 'nullable|in:masuk,keluar',
        ]);

        $tipe_rekap  = $validated['tipe'];
        $tahun       = (int) $validated['tahun'];
        $bulan       = isset($validated['bulan']) ? (int) $validated['bulan'] : null;
        $filterJenis = $validated['jenis'] ?? null;

        $months = [
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];
        $bulan_nama = $bulan ? ($months[$bulan] ?? null) : null;

        $totalSuratMasukBulan  = 0;
        $totalSuratKeluarBulan = 0;
        $surat                 = null;
        $data_laporan          = collect();

        // DATA REKAP TAHUNAN
        if ($tipe_rekap === 'Tahun' || ($tipe_rekap === 'Bulan' && !$bulan)) {

            $perMonth = Laporan::aggregatePerMonth($tahun);

            for ($m = 1; $m <= 12; $m++) {
                $row = $perMonth->get($m);
                $data_laporan->push((object) [
                    'bulan'        => $m,
                    'bulan_nama'   => $months[$m],
                    'total_masuk'  => $row ? (int) $row->total_masuk  : 0,
                    'total_keluar' => $row ? (int) $row->total_keluar : 0,
                ]);
            }

        // DATA DETAIL BULANAN
        } elseif ($tipe_rekap === 'Bulan' && $bulan) {

            $surat = Laporan::getSuratForMonth($tahun, $bulan, null, $filterJenis);

            if ($filterJenis) {
                $totalFiltered = $surat->count();

                if ($filterJenis === 'masuk') {
                    $totalSuratMasukBulan  = $totalFiltered;
                    $totalSuratKeluarBulan = 0;
                } else {
                    $totalSuratMasukBulan  = 0;
                    $totalSuratKeluarBulan = $totalFiltered;
                }
            } else {
                $totals = Laporan::getMonthlyTotals($tahun, $bulan);
                $totalSuratMasukBulan  = $totals->total_masuk  ?? 0;
                $totalSuratKeluarBulan = $totals->total_keluar ?? 0;
            }
        }

        // Nama file
        if ($tipe_rekap === 'Tahun' || ($tipe_rekap === 'Bulan' && !$bulan)) {
            $filenameBase = 'laporan e-arsip surat ' . $tahun;
        } else {
            $bulanLabel   = $bulan_nama ? strtolower($bulan_nama) : 'bulan';
            $filenameBase = 'laporan e-arsip surat ' . $tahun . ' (' . $bulanLabel . ')';
        }

        $view_data = [
            'tipe_rekap'            => $tipe_rekap,
            'tahun'                 => $tahun,
            'bulan'                 => $bulan,
            'bulan_nama'            => $bulan_nama,
            'filterJenis'           => $filterJenis,
            'data_laporan'          => $data_laporan,
            'surat'                 => $surat,
            'totalSuratMasukBulan'  => $totalSuratMasukBulan,
            'totalSuratKeluarBulan' => $totalSuratKeluarBulan,
        ];

        switch (strtolower($format)) {
            case 'excel':
                return $this->exportExcel($view_data, $filenameBase);
            case 'word':
                return $this->exportWord($view_data, $filenameBase);
            case 'pdf':
            default:
                return $this->exportPdf($view_data, $filenameBase);
        }
    }

    /**
     * Export PDF — F4 landscape via DomPDF.
     */
    protected function exportPdf(array $data, string $filenameBase = 'laporan')
    {
        // F4 landscape: 210 x 330 mm  →  595.28 x 935.43 pt
        $f4Landscape = [0, 0, 935.43, 595.28];

        $pdf = Pdf::loadView('laporan.partials.export', $data)
                  ->setPaper($f4Landscape);

        return $pdf->stream($filenameBase . '.pdf');
    }

    /**
     * Export Excel (.xlsx) via Laravel Excel.
     */
    protected function exportExcel(array $data, string $filenameBase = 'laporan')
    {
        return Excel::download(new LaporanExport($data), $filenameBase . '.xlsx');
    }

    /**
     * Export Word (.doc) — render HTML sebagai file Word.
     */
    protected function exportWord(array $data, string $filenameBase = 'laporan')
    {
        $html     = view('laporan.partials.export', $data)->render();
        $filename = $filenameBase . '.doc';

        return new StreamedResponse(function () use ($html) {
            echo $html;
        }, 200, [
            'Content-Type'        => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}