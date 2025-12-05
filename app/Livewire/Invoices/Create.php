<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Service;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    // Invoice data
    public $invoice = [
        'invoice_number' => '',
        'client_id' => null,
        'issue_date' => null,
        'due_date' => null,
    ];

    // Items untuk save
    public $items = [];
    public $discount = [
        'type' => 'fixed',
        'value' => 0,
        'reason' => '',
        'amount' => 0
    ];

    public function save()
    {
        $this->validate([
            'invoice.client_id' => 'required|exists:clients,id',
            'invoice.issue_date' => 'required|date',
            'invoice.due_date' => 'required|date|after_or_equal:invoice.issue_date',
            'items' => 'required|array|min:1',
            'items.*.client_id' => 'required|exists:clients,id',
            'items.*.service_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required',
            'discount.type' => 'in:fixed,percentage',
            'discount.value' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $parsedItems = [];
            $subtotal = 0;

            foreach ($this->items as $item) {
                $unitPrice = $this->parseAmount($item['unit_price']);
                $quantity = $item['quantity'];
                $amount = $unitPrice * $quantity;
                $cogsAmount = $this->parseAmount($item['cogs_amount'] ?? '0');
                $isTaxDeposit = $item['is_tax_deposit'] ?? false;

                $parsedItems[] = [
                    'client_id' => $item['client_id'],
                    'service_name' => $item['service_name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'amount' => $amount,
                    'cogs_amount' => $cogsAmount,
                    'is_tax_deposit' => $isTaxDeposit,
                ];

                $subtotal += $amount;
            }

            // Calculate discount
            $discountAmount = 0;
            $discountValue = 0;
            if ($this->discount['type'] === 'fixed') {
                $discountValue = $this->discount['value'];
                $discountAmount = $this->discount['value'];
            } else {
                $discountValue = $this->discount['value'];
                $discountAmount = ($subtotal * $this->discount['value']) / 100;
            }

            $totalAmount = max(0, $subtotal - $discountAmount);

            $invoiceNumber = $this->invoice['invoice_number'];

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'billed_to_id' => $this->invoice['client_id'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_type' => $this->discount['type'] ?? 'fixed',
                'discount_value' => $discountValue,
                'discount_reason' => $this->discount['reason'],
                'total_amount' => $totalAmount,
                'issue_date' => $this->invoice['issue_date'],
                'due_date' => $this->invoice['due_date'],
                'status' => 'draft',
            ]);

            foreach ($parsedItems as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'client_id' => $itemData['client_id'],
                    'service_name' => $itemData['service_name'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'amount' => $itemData['amount'],
                    'cogs_amount' => $itemData['cogs_amount'],
                    'is_tax_deposit' => $itemData['is_tax_deposit'],
                ]);
            }

            DB::commit();

            session()->flash('success', "Invoice {$invoiceNumber} created successfully!");

            $this->reset(['invoice', 'items', 'discount']);

            return $this->redirect(request()->header('Referer'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create invoice: ' . $e->getMessage());
            session()->flash('error', 'Failed to create invoice. Please try again.');
        }
    }

    #[Computed]
    public function maxInvoiceSequence()
    {
        $date = now();
        $currentYear = $date->year;

        $invoices = Invoice::whereYear('issue_date', $currentYear)
            ->pluck('invoice_number');

        $maxSequence = 0;
        foreach ($invoices as $invoiceNumber) {
            if (preg_match('/^(\d+)\//', $invoiceNumber, $matches)) {
                $sequence = (int) $matches[1];
                $maxSequence = max($maxSequence, $sequence);
            }
        }

        return $maxSequence;
    }

    #[Computed]
    public function invoiceNumberFormat()
    {
        return [
            'company_abbr' => 'AGSA', // atau dari config
            'month_romans' => ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII']
        ];
    }

    private function parseAmount($value): int
    {
        if (empty($value))
            return 0;
        return (int) preg_replace('/[^0-9]/', '', $value);
    }

    #[Computed]
    public function clients()
    {
        return Client::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'logo'])
            ->toArray();
    }

    #[Computed]
    public function services()
    {
        return Service::orderBy('name')
            ->get(['id', 'name', 'price', 'type'])
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price,
                    'type' => $service->type,
                    'formatted_price' => 'Rp ' . number_format($service->price, 0, ',', '.')
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.invoices.create');
    }
}