<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use TallStackUi\Traits\Interactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class Listing extends Component
{
    use WithPagination, Interactions;

    // Table properties
    public array $selected = [];
    public array $sort = ['column' => 'invoice_number', 'direction' => 'desc'];
    public $quantity = 25;

    // Filters
    public $statusFilter = null;
    public $clientFilter = null;
    public $selectedMonth = '2025-12';
    public $dateRange = [];
    public $search = '';

    // Print Modal
    public bool $printModal = false;
    public $printInvoiceId = null;
    public $printTotalAmount = 0;
    public $printAmountPaid = 0;
    public $printType = 'full';
    public $dpAmount = null;

    public array $headers = [
        ['index' => 'invoice_number', 'label' => 'No. Invoice'],
        ['index' => 'client_name', 'label' => 'Klien'],
        ['index' => 'issue_date', 'label' => 'Tanggal'],
        ['index' => 'due_date', 'label' => 'Jatuh Tempo'],
        ['index' => 'total_amount', 'label' => 'Jumlah'],
        ['index' => 'status', 'label' => 'Status'],
        ['index' => 'actions', 'label' => 'Aksi', 'sortable' => false],
    ];

    protected $listeners = [
        'invoice-created' => '$refresh',
        'invoice-updated' => '$refresh',
        'invoice-deleted' => '$refresh',
        'invoice-sent' => '$refresh'
    ];

    public function mount()
    {
        $this->dateRange = [];
        $this->dispatchFilterChange();
    }

    // Open print modal
    public function openPrintModal(int $invoiceId, int $totalAmount, int $amountPaid = 0): void
    {
        $this->printInvoiceId = $invoiceId;
        $this->printTotalAmount = $totalAmount;
        $this->printAmountPaid = $amountPaid;
        $this->printType = 'full';
        $this->dpAmount = null;
        $this->printModal = true;
    }

    // Execute print
    public function executePrint(): void
    {
        if (!$this->printInvoiceId) {
            $this->toast()->error('Error', 'Invoice tidak ditemukan')->send();
            return;
        }

        $previewUrl = route('invoice.preview', $this->printInvoiceId);
        $downloadUrl = route('invoice.download', $this->printInvoiceId);

        if ($this->printType === 'dp') {
            $dpParsed = $this->dpAmount ? (int) preg_replace('/[^0-9]/', '', $this->dpAmount) : 0;

            if ($dpParsed <= 0 || $dpParsed > $this->printTotalAmount) {
                $this->toast()->error('Error', 'Nominal DP tidak valid')->send();
                return;
            }

            $previewUrl .= '?dp_amount=' . $dpParsed;
            $downloadUrl .= '?dp_amount=' . $dpParsed;
        } elseif ($this->printType === 'pelunasan') {
            $sisaPembayaran = $this->printTotalAmount - $this->printAmountPaid;

            if ($sisaPembayaran <= 0) {
                $this->toast()->error('Error', 'Invoice sudah lunas')->send();
                return;
            }

            $previewUrl .= '?pelunasan_amount=' . $sisaPembayaran;
            $downloadUrl .= '?pelunasan_amount=' . $sisaPembayaran;
        }

        $this->dispatch('execute-print', [
            'previewUrl' => $previewUrl,
            'downloadUrl' => $downloadUrl
        ]);

        $this->printModal = false;
        $this->reset(['printInvoiceId', 'printTotalAmount', 'printAmountPaid', 'printType', 'dpAmount']);
    }

    #[Computed]
    public function invoices(): LengthAwarePaginator
    {
        $query = Invoice::query()
            ->join('clients', 'invoices.billed_to_id', '=', 'clients.id')
            ->leftJoin('payments', 'invoices.id', '=', 'payments.invoice_id')
            ->select([
                'invoices.*',
                'clients.name as client_name',
                'clients.type as client_type',
                DB::raw('COALESCE(SUM(payments.amount), 0) as amount_paid')
            ])
            ->groupBy([
                'invoices.id',
                'invoices.invoice_number',
                'invoices.billed_to_id',
                'invoices.total_amount',
                'invoices.issue_date',
                'invoices.due_date',
                'invoices.status',
                'invoices.created_at',
                'invoices.updated_at',
                'invoices.subtotal',
                'invoices.discount_amount',
                'invoices.discount_type',
                'invoices.discount_value',
                'invoices.discount_reason',
                'clients.name',
                'clients.type'
            ]);

        $query->when($this->search, function ($q) {
            $q->where(function ($query) {
                $query->where('invoices.invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhere('clients.name', 'like', '%' . $this->search . '%');
            });
        });

        $query->when($this->statusFilter, fn($q) => $q->where('invoices.status', $this->statusFilter));
        $query->when($this->clientFilter, fn($q) => $q->where('invoices.billed_to_id', $this->clientFilter));

        $query->when(
            $this->dateRange && count($this->dateRange) >= 2 && $this->dateRange[0] && $this->dateRange[1],
            fn($q) => $q->whereBetween('invoices.issue_date', [
                $this->dateRange[0],
                $this->dateRange[1]
            ])
        )->unless(
                $this->dateRange,
                fn($q) => $q->when(
                    $this->selectedMonth,
                    fn($query) => $query->whereYear('invoices.issue_date', substr($this->selectedMonth, 0, 4))
                        ->whereMonth('invoices.issue_date', substr($this->selectedMonth, 5, 2))
                )
            );

        match ($this->sort['column']) {
            'client_name' => $query->orderBy('clients.name', $this->sort['direction']),
            'invoice_number', 'issue_date', 'due_date', 'total_amount', 'status' =>
            $query->orderBy('invoices.' . $this->sort['column'], $this->sort['direction']),
            default => $query->orderBy(...array_values($this->sort))
        };

        return $query->paginate($this->quantity)->withQueryString();
    }

    #[Computed]
    public function clients()
    {
        return Client::select('id', 'name')->orderBy('name')->get();
    }

    protected function dispatchFilterChange(): void
    {
        $this->dispatch('filter-changed', [
            'statusFilter' => $this->statusFilter,
            'clientFilter' => $this->clientFilter,
            'selectedMonth' => $this->selectedMonth,
            'dateRange' => $this->dateRange,
            'search' => $this->search,
        ]);
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        $this->dispatchFilterChange();
    }

    public function updatedClientFilter()
    {
        $this->resetPage();
        $this->dispatchFilterChange();
    }

    public function updatedSelectedMonth()
    {
        $this->resetPage();
        $this->dispatchFilterChange();
    }

    public function updatedDateRange()
    {
        $this->resetPage();
        $this->dispatchFilterChange();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->dispatchFilterChange();
    }

    public function showInvoice(int $invoiceId): void
    {
        $this->dispatch('show-invoice', invoiceId: $invoiceId);
    }

    public function recordPayment(int $invoiceId): void
    {
        $this->dispatch('record-payment', invoiceId: $invoiceId);
    }

    public function deleteInvoice(int $invoiceId): void
    {
        $this->dispatch('delete-invoice', invoiceId: $invoiceId);
    }

    public function sendInvoice(int $invoiceId): void
    {
        try {
            $invoice = Invoice::find($invoiceId);

            if (!$invoice || $invoice->status !== 'draft') {
                $this->toast()->warning('Warning', 'Hanya invoice draft yang bisa dikirim')->send();
                return;
            }

            $invoice->update(['status' => 'sent']);
            $this->toast()->success('Berhasil', "Invoice {$invoice->invoice_number} berhasil dikirim")->send();
            $this->dispatch('invoice-sent');
        } catch (\Exception $e) {
            $this->toast()->error('Error', 'Gagal mengirim invoice: ' . $e->getMessage())->send();
        }
    }

    public function bulkPrintInvoices(): void
    {
        if (empty($this->selected)) {
            $this->toast()->warning('Warning', 'Pilih minimal satu invoice untuk di-print')->send();
            return;
        }

        try {
            $invoices = Invoice::whereIn('id', $this->selected)->get(['id', 'invoice_number']);

            if ($invoices->isEmpty()) {
                $this->toast()->error('Error', 'Invoice tidak ditemukan')->send();
                return;
            }

            $this->dispatch('start-bulk-download', [
                'downloads' => $invoices->map(fn($invoice) => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'url' => route('invoice.download', $invoice->id)
                ])->toArray(),
                'delay' => 2000,
                'method' => 'iframe'
            ]);

            $this->selected = [];
            $this->toast()->success('Download Dimulai', "Mengunduh {$invoices->count()} invoice PDF")->send();
        } catch (\Exception $e) {
            $this->toast()->error('Error', 'Gagal memulai download: ' . $e->getMessage())->send();
        }
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->toast()->warning('Warning', 'Pilih minimal satu invoice untuk dihapus')->send();
            return;
        }

        try {
            DB::transaction(function () {
                \App\Models\InvoiceItem::whereIn('invoice_id', $this->selected)->delete();
                \App\Models\Invoice::whereIn('id', $this->selected)->delete();
            });

            $deletedCount = count($this->selected);
            $this->selected = [];
            $this->toast()->success('Berhasil', "Berhasil menghapus {$deletedCount} invoice")->send();
            $this->dispatch('invoice-deleted');
        } catch (\Exception $e) {
            $this->toast()->error('Error', 'Gagal menghapus: ' . $e->getMessage())->send();
        }
    }

    public function rollbackTodraft(int $invoiceId): void
    {
        try {
            $invoice = Invoice::find($invoiceId);

            if (!$invoice || $invoice->status !== 'sent') {
                $this->toast()->warning('Warning', 'Hanya invoice terkirim yang bisa dikembalikan ke draft')->send();
                return;
            }

            $invoice->update(['status' => 'draft']);
            $this->toast()->success('Berhasil', "Invoice {$invoice->invoice_number} dikembalikan ke draft")->send();
            $this->dispatch('invoice-updated');
        } catch (\Exception $e) {
            $this->toast()->error('Error', 'Gagal rollback: ' . $e->getMessage())->send();
        }
    }

    public function clearFilters(): void
    {
        $this->fill([
            'statusFilter' => null,
            'clientFilter' => null,
            'selectedMonth' => null,
            'dateRange' => []
        ]);
        $this->resetPage();
        $this->dispatchFilterChange();
    }

    public function exportExcel()
    {
        return (new \App\Services\InvoiceExportService())->exportExcel($this->getFilters());
    }

    public function exportPdf()
    {
        $service = new \App\Services\InvoiceExportService();
        return response()->streamDownload(
            fn() => print $service->exportPdf($this->getFilters())->output(),
            'invoices-' . now()->format('Y-m-d') . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function getFilters(): array
    {
        return [
            'statusFilter' => $this->statusFilter,
            'clientFilter' => $this->clientFilter,
            'selectedMonth' => $this->selectedMonth,
            'dateRange' => $this->dateRange,
        ];
    }

    public function render()
    {
        return view('livewire.invoices.listing');
    }
}