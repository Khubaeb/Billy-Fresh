<?php

namespace App\Exports;

use App\Models\Invoice;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class IncomeExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Invoice::whereBetween('invoice_date', [
                $this->startDate->format('Y-m-d'), 
                $this->endDate->format('Y-m-d')
            ])
            ->with('customer')
            ->orderBy('invoice_date', 'desc')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Invoice #',
            'Customer',
            'Date',
            'Due Date',
            'Status',
            'Subtotal',
            'Tax',
            'Total',
            'Paid',
            'Due'
        ];
    }

    /**
     * @param Invoice $invoice
     * @return array
     */
    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->customer->name,
            $invoice->invoice_date->format('M d, Y'),
            $invoice->due_date->format('M d, Y'),
            ucfirst($invoice->status),
            '$' . number_format($invoice->subtotal, 2),
            '$' . number_format($invoice->tax_amount, 2),
            '$' . number_format($invoice->total, 2),
            '$' . number_format($invoice->amount_paid, 2),
            '$' . number_format($invoice->amount_due, 2)
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Income Report';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
