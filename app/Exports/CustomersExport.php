<?php

namespace App\Exports;

use App\Models\Customer;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
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
        return Customer::withCount(['invoices' => function($query) {
                $query->whereBetween('invoice_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);
            }])
            ->withSum(['invoices' => function($query) {
                $query->whereBetween('invoice_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);
            }], 'total')
            ->withSum(['invoices' => function($query) {
                $query->whereBetween('invoice_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);
            }], 'amount_paid')
            ->orderByDesc('invoices_total')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Customer Name',
            'Company',
            'Email',
            'Phone',
            'Invoices Count',
            'Total Amount',
            'Paid Amount',
            'Outstanding',
            'Payment Rate'
        ];
    }

    /**
     * @param Customer $customer
     * @return array
     */
    public function map($customer): array
    {
        $outstanding = max(0, $customer->invoices_total - $customer->invoices_amount_paid);
        $paymentRate = $customer->invoices_total > 0 
            ? round(($customer->invoices_amount_paid / $customer->invoices_total) * 100) 
            : 0;
            
        return [
            $customer->name,
            $customer->company_name ?? 'N/A',
            $customer->email,
            $customer->phone,
            $customer->invoices_count,
            '$' . number_format($customer->invoices_total, 2),
            '$' . number_format($customer->invoices_amount_paid, 2),
            '$' . number_format($outstanding, 2),
            $paymentRate . '%'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Customer Report';
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
