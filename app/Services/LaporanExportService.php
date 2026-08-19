<?php

namespace App\Services;

use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Ekspor laporan ke file Excel (.xlsx) — tiap laporan menjadi satu sheet.
 * Menangani styling kop laporan, header tabel, dan proteksi formula injection.
 */
class LaporanExportService
{
    /**
     * Tulis satu atau banyak laporan ke file .xlsx lalu kirim sebagai unduhan.
     * Setiap laporan menjadi satu sheet.
     */
    public function streamXlsx(array $reports, string $fileName)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'laporan_');
        $path = ($tmp === false ? sys_get_temp_dir().'/laporan_'.uniqid() : $tmp).'.xlsx';

        try {
            $writer = new Writer;
            $writer->openToFile($path);

            $titleStyle = (new Style)
                ->setFontBold()
                ->setFontSize(13)
                ->setFontColor('0F766E');

            $headerStyle = (new Style)
                ->setFontBold()
                ->setBackgroundColor('E6F4F2');

            $lastIndex = count($reports) - 1;

            foreach ($reports as $i => $report) {
                $sheet = $writer->getCurrentSheet();
                $sheet->setName($this->sheetName($report['judul']));

                // Kop laporan
                $writer->addRow(Row::fromValues([strtoupper($report['judul'])], $titleStyle));
                $writer->addRow(Row::fromValues([$report['updated']]));
                $writer->addRow(Row::fromValues([$report['dibuat']]));
                $writer->addRow(Row::fromValues([$report['periode']]));
                $writer->addRow(Row::fromValues([]));

                // Ringkasan
                foreach ($report['summary'] as $s) {
                    $writer->addRow(Row::fromValues([
                        $this->xlsxSafe($s['label']),
                        $this->xlsxSafe($s['value']),
                    ]));
                }
                $writer->addRow(Row::fromValues([]));

                // Header tabel
                $writer->addRow(Row::fromValues($report['columns'], $headerStyle));

                // Data
                $rows = $report['rows'];
                if (empty($rows)) {
                    $writer->addRow(Row::fromValues(['Belum ada data.']));
                }
                foreach ($rows as $row) {
                    $writer->addRow(Row::fromValues(array_map([$this, 'xlsxSafe'], $row)));
                }

                // Lebar kolom agar mudah dibaca di Excel
                $sheet->setColumnWidth(28, 1, 2, 3, 4, 5);

                if ($i < $lastIndex) {
                    $writer->addNewSheetAndMakeItCurrent();
                }
            }

            $writer->close();
        } catch (\Throwable $e) {
            // Gagal: bersihkan file xlsx yang mungkin parsial
            @unlink($path);
            throw $e;
        } finally {
            if ($tmp !== false) {
                @unlink($tmp);
            }
        }

        return response()->download($path, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function sheetName(string $judul): string
    {
        // Nama sheet maks 31 karakter & tidak boleh mengandung \ / ? * [ ] :
        $name = Str::slug($judul, '_');

        return Str::limit($name, 31, '');
    }

    /** Cegah formula injection: sel diawali =, +, -, @ diawali tanda kutip. */
    private function xlsxSafe($value)
    {
        if (is_string($value) && preg_match('/^[=+\-@]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
