<?php

namespace App\Services;

use App\Models\CompanyProfile;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Ngekoding\Terbilang\Terbilang;

class InvoicePrintService
{
    public function generateSingleInvoicePdf(Invoice $invoice, ?int $dpAmount = null, ?int $pelunasanAmount = null)
    {
        $invoice->load(['client', 'items.client', 'payments.bankAccount']);
        $company = CompanyProfile::current();

        $isDownPayment = !is_null($dpAmount) && $dpAmount > 0;
        $isPelunasan = !is_null($pelunasanAmount) && $pelunasanAmount > 0;
        $displayAmount = $isDownPayment ? $dpAmount : ($isPelunasan ? $pelunasanAmount : $invoice->total_amount);

        $regularItems = $invoice->items->where('is_tax_deposit', false);
        $taxDepositItems = $invoice->items->where('is_tax_deposit', true);

        // Perhitungan AGSA Invoice
        $subtotalI = $invoice->subtotal;
        $dpp = $regularItems->sum('amount'); // Total non-tax deposit items
        $ppn = $dpp * 0.11; // 11% dari DPP
        $subtotalII = $subtotalI + $ppn;
        $pph23 = $dpp * 0.02; // 2% dari DPP
        $grandTotal = $subtotalII - $pph23;

        $data = [
            'invoice' => $invoice,
            'client' => $invoice->client,
            'items' => $invoice->items,
            'regular_items' => $regularItems,
            'tax_deposit_items' => $taxDepositItems,
            'payments' => $invoice->payments,
            'company' => $this->getCompanyInfo($company),

            // AGSA Calculations
            'subtotalI' => $subtotalI,
            'dpp' => $dpp,
            'ppn' => $ppn,
            'subtotalII' => $subtotalII,
            'pph23' => $pph23,
            'grandTotal' => $grandTotal,
            'terbilang' => ucfirst(Terbilang::convert($grandTotal, true)),

            'is_down_payment' => $isDownPayment,
            'is_pelunasan' => $isPelunasan,
            'dp_amount' => $dpAmount,
            'pelunasan_amount' => $pelunasanAmount,
            'total_paid' => $invoice->payments->sum('amount'),
        ];

        return Pdf::loadView('pdf.agsa-invoice', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
    }

    public function downloadSingleInvoice(Invoice $invoice)
    {
        $filename = 'Invoice-' . str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $invoice->invoice_number) . '.pdf';
        return $this->generateSingleInvoicePdf($invoice)->download($filename);
    }

    private function getCompanyInfo(?CompanyProfile $company): array
    {
        if (!$company) {
            return $this->getFallbackCompanyInfo();
        }

        return [
            'name' => $company->name,
            'address' => $company->address,
            'email' => $company->email,
            'phone' => $company->phone,
            'logo_base64' => $company?->logo_path ? $this->getImageBase64($company->logo_path) : '',
            'signature_base64' => $company?->signature_path ? $this->getImageBase64($company->signature_path) : '',
            'stamp_base64' => $company?->stamp_path ? $this->getImageBase64($company->stamp_path) : '',
            'bank_accounts' => $company->bank_accounts,
            'signature' => [
                'name' => $company->finance_manager_name,
                'position' => $company->finance_manager_position
            ],
            'is_pkp' => $company->is_pkp,
            'npwp' => $company->npwp,
            'ppn_rate' => $company->ppn_rate,
        ];
    }

    private function getFallbackCompanyInfo(): array
    {
        return [
            'name' => 'PT. KINARA SADAYATRA NUSANTARA',
            'address' => 'Jl. A. Wahab Syahranie Perum Pondok Alam Indah, Nomor 3D, Kel. Sempaja Barat, Kota Samarinda - Kalimantan Timur',
            'email' => 'kisantra.official@gmail.com',
            'phone' => '0852-8888-2600',
            'logo_base64' => $this->getImageBase64('images/letter-head.png'),
            'signature_base64' => $this->getImageBase64('images/pdf-signature.png'),
            'stamp_base64' => $this->getImageBase64('images/kisantra-stamp.png'),
            'bank_accounts' => [
                ['bank' => 'MANDIRI', 'account_number' => '1480045452425', 'account_name' => 'PT. KINARA SADAYATRA NUSANTARA']
            ],
            'signature' => ['name' => 'Mohammad Denny Jodysetiawan', 'position' => 'Manajer Keuangan'],
            'is_pkp' => false,
            'npwp' => null,
            'ppn_rate' => 11.00,
        ];
    }

    private function getImageBase64(string $path): string
    {
        $fullPath = public_path('storage/' . $path);
        return file_exists($fullPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($fullPath)) : '';
    }

    // ← Hapus method numberToWords() yang lama
}