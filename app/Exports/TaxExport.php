<?php

namespace App\Exports;

use App\Models\Invoice;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TaxExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new TaxSummarySheet($this->startDate, $this->endDate),
            new TaxCollectedSheet($this->startDate, $this->endDate),
            new TaxPaidSheet($this->startDate, $this->endDate),
        ];
    }
}

class TaxSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Calculate monthly tax summary
        $months = [];
        $currentDate = $this->startDate->copy()->startOfMonth();
        $endDateMonth = $this->endDate->copy()->startOfMonth();
        
        while ($currentDate->lte($endDateMonth)) {
            $monthKey = $currentDate->format('F Y');
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();
            
            $collected = Invoice::whereBetween('invoice_date', [
                $monthStart->format('Y-m-d'), 
                $monthEnd->format('Y-m-d')
            ])->sum('tax_amount');
            
            $paid = Expense::whereBetween('expense_date', [
                $monthStart->format('Y-m-d'), 
                $monthEnd->format('Y-m-d')
            ])->sum('tax_amount');
            
            $net = $collected - $paid;
            
            $months[] = [
                'month' => $monthKey,
                'collected' => $collected,
                'paid' => $paid,
                'net' => $net
            ];
            
            $currentDate->addMonth();
        }
        
        return new Collection($months);
    }

    public function headings(): array
    {
        return [
            'Month',
            'Tax Collected',
            'Tax Paid',
            'Net Tax'
        ];
    }

    public function title(): string
    {
        return 'Tax Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class TaxCollectedSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Invoice::whereBetween('invoice_date', [
                $this->startDate->format('Y-m-d'), 
                $this->endDate->format('Y-m-d')
            ])
            ->where('tax_amount', '>', 0)
            ->with('customer')
            ->orderBy('invoice_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Invoice #',
            'Date',
            'Customer',
            'Invoice Amount',
            'Tax Rate',
            'Tax Amount'
        ];
    }

    public function map($invoice): array
    {
        $taxRate = ($invoice->tax_amount > 0 && $invoice->subtotal > 0) 
            ? round(($invoice->tax_amount / $invoice->subtotal) * 100, 2) . '%' 
            : 'N/A';
            
        return [
            $invoice->invoice_number,
            $invoice->invoice_date->format('M d, Y'),
            $invoice->customer->name,
            '$' . number_format($invoice->total, 2),
            $taxRate,
            '$' . number_format($invoice->tax_amount, 2)
        ];
    }

    public function title(): string
    {
        return 'Tax Collected';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class TaxPaidSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Expense::whereBetween('expense_date', [
                $this->startDate->format('Y-m-d'), 
                $this->endDate->format('Y-m-d')
            ])
            ->where('tax_amount', '>', 0)
            ->orderBy('expense_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Category',
            'Vendor',
            'Reference',
            'Expense Amount',
            'Tax Rate',
            'Tax Amount'
        ];
    }

    public function map($expense): array
    {
        $taxRate = ($expense->tax_amount > 0 && ($expense->amount - $expense->tax_amount) > 0) 
            ? round(($expense->tax_amount / ($expense->amount - $expense->tax_amount)) * 100, 2) . '%' 
            : 'N/A';
            
        return [
            $expense->expense_date->format('M d, Y'),
            $expense->category,
            $expense->vendor,
            $expense->reference_number,
            '$' . number_format($expense->amount, 2),
            $taxRate,
            '$' . number_format($expense->tax_amount, 2)
        ];
    }

    public function title(): string
    {
        return 'Tax Paid';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
