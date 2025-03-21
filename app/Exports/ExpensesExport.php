<?php

namespace App\Exports;

use App\Models\Expense;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
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
        return Expense::whereBetween('expense_date', [
                $this->startDate->format('Y-m-d'), 
                $this->endDate->format('Y-m-d')
            ])
            ->orderBy('expense_date', 'desc')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Date',
            'Category',
            'Vendor',
            'Description',
            'Reference',
            'Amount',
            'Tax Amount',
            'Total',
            'Billable'
        ];
    }

    /**
     * @param Expense $expense
     * @return array
     */
    public function map($expense): array
    {
        return [
            $expense->expense_date->format('M d, Y'),
            $expense->category,
            $expense->vendor,
            $expense->description,
            $expense->reference_number,
            '$' . number_format($expense->amount - $expense->tax_amount, 2),
            '$' . number_format($expense->tax_amount, 2),
            '$' . number_format($expense->amount, 2),
            $expense->is_billable ? 'Yes' : 'No'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Expense Report';
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
