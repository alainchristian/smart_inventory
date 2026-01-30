<?php

namespace App\Services\Inventory;

use App\Models\Box;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeService
{
    public function generateBoxCode(): string
    {
        // Format: BOX-YYYYMMDD-XXXXX
        $date = now()->format('Ymd');
        $sequence = Box::whereDate('created_at', today())->count() + 1;
        $paddedSequence = str_pad($sequence, 5, '0', STR_PAD_LEFT);

        $code = "BOX-{$date}-{$paddedSequence}";

        // Ensure uniqueness
        while (Box::where('box_code', $code)->exists()) {
            $sequence++;
            $paddedSequence = str_pad($sequence, 5, '0', STR_PAD_LEFT);
            $code = "BOX-{$date}-{$paddedSequence}";
        }

        return $code;
    }

    public function generateBarcodeImage(string $code, int $widthFactor = 2, int $height = 50): string
    {
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128, $widthFactor, $height);

        return 'data:image/png;base64,' . base64_encode($barcode);
    }

    public function generateBarcodeSVG(string $code): string
    {
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        return $generator->getBarcode($code, $generator::TYPE_CODE_128);
    }

    public function validateBoxCode(string $code): bool
    {
        // Validate format: BOX-YYYYMMDD-XXXXX
        return (bool) preg_match('/^BOX-\d{8}-\d{5}$/', $code);
    }

    public function printBarcodeLabel(Box $box): string
    {
        // Generate PDF label
        $pdf = app('dompdf.wrapper');

        $html = view('pdfs.box-label', [
            'box' => $box,
            'barcode' => $this->generateBarcodeImage($box->box_code),
            'product' => $box->product,
        ])->render();

        $pdf->loadHTML($html);
        $pdf->setPaper([0, 0, 288, 144], 'portrait'); // 4" x 2" label

        return $pdf->output();
    }

    public function bulkPrintLabels(array $boxIds): string
    {
        $boxes = Box::with('product')->findMany($boxIds);

        $pdf = app('dompdf.wrapper');

        $html = view('pdfs.box-labels-bulk', [
            'boxes' => $boxes,
            'barcodeService' => $this,
        ])->render();

        $pdf->loadHTML($html);

        return $pdf->output();
    }
}
