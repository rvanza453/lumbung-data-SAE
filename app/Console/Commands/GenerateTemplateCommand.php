<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GenerateTemplateCommand extends Command
{
    protected $signature = 'template:generate';
    protected $description = 'Generate Excel template for data upload';

    public function handle()
    {
        $this->info('Generating Excel template...');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Upload');

        // Header
        $headers = ['AFD', 'NAMA', 'HK', 'JJG', 'TON', 'PROD', 'ID'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Sample data
        $sampleData = [
            ['I', 'Budi Santoso', 25, 1500, 22.5, 900, 1001],
            ['II', 'Agus Salim', 22, 1300, 19.5, 886, 1002],
            ['III', 'Siti Aminah', 26, 1600, 24.0, 923, 1003],
            ['I', 'Rudi Hartono', 20, 1200, 18.0, 900, 1004],
            ['IV', 'Dewi Sartika', 24, 1450, 21.75, 906, 1005],
        ];
        
        $sheet->fromArray($sampleData, null, 'A2');

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add instructions sheet
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('Instruksi');
        
        $instructions = [
            ['PANDUAN PENGGUNAAN TEMPLATE'],
            [''],
            ['KOLOM WAJIB:'],
            ['- NAMA: Nama karyawan (wajib diisi)'],
            ['- HK: Jumlah hari kerja (wajib diisi, angka)'],
            ['- JJG: Jumlah janjang (wajib diisi, angka)'],
            [''],
            ['KOLOM OPSIONAL:'],
            ['- AFD: Afdeling/Divisi'],
            ['- TON: Tonase (jika kosong, akan dihitung otomatis dari JJG x BJR)'],
            ['- PROD: Produktivitas Kg/HK (jika kosong, akan dihitung otomatis)'],
            ['- ID: NIK/ID karyawan'],
            [''],
            ['TIPS:'],
            ['- Hapus data contoh sebelum input data real'],
            ['- Pastikan HK dan JJG berisi angka'],
            ['- Format kolom tidak boleh diubah'],
            ['- Maksimal 10,000 baris per file'],
            [''],
            ['CONTOH DATA:'],
            ['AFD: I, II, III, IV, dst'],
            ['NAMA: Nama lengkap karyawan'],
            ['HK: 25 (jumlah hari kerja)'],
            ['JJG: 1500 (jumlah janjang)'],
            ['TON: 22.5 (opsional, atau biarkan kosong)'],
            ['PROD: 900 (opsional, atau biarkan kosong)'],
            ['ID: 1001 (opsional)'],
        ];
        
        $instructionSheet->fromArray($instructions, null, 'A1');
        $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $instructionSheet->getColumnDimension('A')->setWidth(60);

        // Save file
        $writer = new Xlsx($spreadsheet);
        $filePath = public_path('template-upload.xlsx');
        $writer->save($filePath);

        $this->info('Template generated successfully at: ' . $filePath);
        
        return 0;
    }
}

