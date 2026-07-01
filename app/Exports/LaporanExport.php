<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanExport implements FromView, ShouldAutoSize, WithEvents, WithStyles
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        // pakai blade khusus excel
        return view('laporan.partials.export_excel', $this->data);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]], // judul
            2 => ['font' => ['bold' => true]],               // subjudul
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                // ===========================
                // 1. Merge judul & subjudul
                // ===========================
                $sheet->mergeCells("A1:{$highestCol}1");
                $sheet->mergeCells("A2:{$highestCol}2");
                $sheet->getStyle("A1:A2")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // ===========================
                // 2. Deteksi header tabel:
                //    - Ringkasan: A="No", B="Bulan"
                //    - Detail   : A="No", B="No Surat"
                // ===========================
                $ringkasanHeaderRow = null;
                $detailHeaderRow    = null;

                for ($row = 1; $row <= $highestRow; $row++) {
                    $a = trim((string) $sheet->getCell("A{$row}")->getValue());
                    $b = trim((string) $sheet->getCell("B{$row}")->getValue());

                    if ($a === 'No' && $b === 'Bulan' && $ringkasanHeaderRow === null) {
                        $ringkasanHeaderRow = $row;
                    }

                    if ($a === 'No' && $b === 'No Surat' && $detailHeaderRow === null) {
                        $detailHeaderRow = $row;
                    }
                }

                // ===========================
                // 3. Range tabel ringkasan
                // ===========================
                if ($ringkasanHeaderRow !== null) {
                    $lastRingkasanRow = $ringkasanHeaderRow + 1;

                    // jalan terus sampai baris kosong pertama / tulisan lain
                    for ($row = $ringkasanHeaderRow + 1; $row <= $highestRow; $row++) {
                        $a = trim((string) $sheet->getCell("A{$row}")->getValue());
                        if ($a === '' || $a === 'Detail Daftar Surat') {
                            break;
                        }
                        $lastRingkasanRow = $row;
                    }

                    $rangeHeaderRingkasan = "A{$ringkasanHeaderRow}:E{$ringkasanHeaderRow}";
                    $rangeTableRingkasan  = "A{$ringkasanHeaderRow}:E{$lastRingkasanRow}";

                    // header ringkasan
                    $sheet->getStyle($rangeHeaderRingkasan)->applyFromArray([
                        'font'      => ['bold' => true],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                        ],
                        'fill'      => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFEFEFEF'],
                        ],
                    ]);

                    // border & vertical center isi ringkasan
                    $sheet->getStyle($rangeTableRingkasan)->applyFromArray([
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,   // biar kalau angka besar / teks panjang tetap rapi
                        ],
                        'borders'   => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }

                // ===========================
                // 4. Range tabel detail (jika ada)
                // ===========================
                if ($detailHeaderRow !== null) {
                    // cari baris terakhir detail: sampai A kosong
                    $lastDetailRow = $detailHeaderRow + 1;
                    for ($row = $detailHeaderRow + 1; $row <= $highestRow; $row++) {
                        $a = trim((string) $sheet->getCell("A{$row}")->getValue());
                        if ($a === '') {
                            break;
                        }
                        $lastDetailRow = $row;
                    }

                    $rangeHeaderDetail = "A{$detailHeaderRow}:J{$detailHeaderRow}";
                    $rangeTableDetail  = "A{$detailHeaderRow}:J{$lastDetailRow}";

                    // header detail
                    $sheet->getStyle($rangeHeaderDetail)->applyFromArray([
                        'font'      => ['bold' => true],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                        ],
                        'fill'      => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFEFEFEF'],
                        ],
                    ]);

                    // border & vertical center isi detail + wrap text
                    $sheet->getStyle($rangeTableDetail)->applyFromArray([
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true, // << responsif: teks panjang turun ke bawah
                        ],
                        'borders'   => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }
            },
        ];
    }
}
