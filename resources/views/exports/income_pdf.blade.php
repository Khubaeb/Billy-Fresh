<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin-bottom: 5px;
            color: #007bff;
        }
        .header p {
            margin-top: 0;
            color: #6c757d;
        }
        .summary {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
        }
        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            width: 30%;
            text-align: center;
        }
        .summary-box h3 {
            margin-top: 0;
            margin-bottom: 5px;
        }
        .summary-box p {
            margin: 0;
            color: #6c757d;
        }
        .total-invoiced {
            color: #007bff;
        }
        .total-paid {
            color: #28a745;
        }
        .total-outstanding {
            color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table th, table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            color: #6c757d;
            font-size: 12px;
        }
        .paid {
            color: #28a745;
        }
        .pending {
            color: #ffc107;
        }
        .overdue {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Income Report</h1>
        <p>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <h3 class="total-invoiced">${{ number_format($totalInvoiced, 2) }}</h3>
            <p>Total Invoiced</p>
        </div>
        <div class="summary-box">
            <h3 class="total-paid">${{ number_format($totalPaid, 2) }}</h3>
            <p>Total Paid ({{ round(($totalPaid / max($totalInvoiced, 0.01)) * 100) }}%)</p>
        </div>
        <div class="summary-box">
            <h3 class="total-outstanding">${{ number_format($totalOutstanding, 2) }}</h3>
            <p>Total Outstanding</p>
        </div>
    </div>

    <h2>Invoices ({{ count($invoices) }})</h2>
    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Due Date</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Due</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
            <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->customer->name }}</td>
                <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                <td>{{ $invoice->due_date->format('M d, Y') }}</td>
                <td class="text-right">${{ number_format($invoice->total, 2) }}</td>
                <td class="text-right">${{ number_format($invoice->amount_paid, 2) }}</td>
                <td class="text-right">${{ number_format($invoice->amount_due, 2) }}</td>
                <td class="{{ $invoice->status }}">
                    {{ ucfirst($invoice->status) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No invoices found for the selected period.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th class="text-right">${{ number_format($totalInvoiced, 2) }}</th>
                <th class="text-right">${{ number_format($totalPaid, 2) }}</th>
                <th class="text-right">${{ number_format($totalOutstanding, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
        <p>Billy - Invoice & Expense Management System</p>
    </div>
</body>
</html>
