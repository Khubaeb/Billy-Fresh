<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Report</title>
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
        .customer-name {
            font-weight: bold;
        }
        .company-name {
            color: #6c757d;
            font-size: 12px;
        }
        .progress-container {
            background-color: #e9ecef;
            height: 8px;
            border-radius: 4px;
            margin-top: 5px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            border-radius: 4px;
        }
        .progress-high {
            background-color: #28a745;
        }
        .progress-medium {
            background-color: #ffc107;
        }
        .progress-low {
            background-color: #dc3545;
        }
        .payment-rate {
            font-size: 12px;
            color: #6c757d;
            margin-top: 2px;
            text-align: right;
        }
        .summary {
            margin-bottom: 30px;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .summary-data {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .summary-item {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
        }
        .summary-item-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-item-value {
            font-size: 18px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Customer Report</h1>
        <p>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-title">Customer Activity Summary</div>
        <div class="summary-data">
            <div class="summary-item">
                <div class="summary-item-title">Total Customers</div>
                <div class="summary-item-value">{{ count($customers) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-title">Total Revenue</div>
                <div class="summary-item-value">${{ number_format($customers->sum('invoices_total'), 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-title">Average per Customer</div>
                <div class="summary-item-value">
                    ${{ number_format($customers->count() > 0 ? $customers->sum('invoices_total') / $customers->count() : 0, 2) }}
                </div>
            </div>
        </div>
    </div>

    <h2>Customer Activity ({{ count($customers) }})</h2>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Contact</th>
                <th class="text-center">Invoices</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Paid Amount</th>
                <th class="text-right">Outstanding</th>
                <th>Payment Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
            <tr>
                <td>
                    <div class="customer-name">{{ $customer->name }}</div>
                    @if($customer->company_name)
                        <div class="company-name">{{ $customer->company_name }}</div>
                    @endif
                </td>
                <td>
                    {{ $customer->email }}<br>
                    {{ $customer->phone }}
                </td>
                <td class="text-center">{{ $customer->invoices_count }}</td>
                <td class="text-right">${{ number_format($customer->invoices_total, 2) }}</td>
                <td class="text-right">${{ number_format($customer->invoices_amount_paid, 2) }}</td>
                <td class="text-right">${{ number_format(max(0, $customer->invoices_total - $customer->invoices_amount_paid), 2) }}</td>
                <td>
                    @php
                        $paymentRate = $customer->invoices_total > 0 ? 
                            round(($customer->invoices_amount_paid / $customer->invoices_total) * 100) : 0;
                        $progressClass = $paymentRate >= 80 ? 'progress-high' : ($paymentRate >= 50 ? 'progress-medium' : 'progress-low');
                    @endphp
                    <div class="progress-container">
                        <div class="progress-bar {{ $progressClass }}" style="width: {{ $paymentRate }}%"></div>
                    </div>
                    <div class="payment-rate">{{ $paymentRate }}%</div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No customer data found for the selected period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
        <p>Billy - Invoice & Expense Management System</p>
    </div>
</body>
</html>
