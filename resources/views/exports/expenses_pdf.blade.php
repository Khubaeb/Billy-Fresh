<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
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
            color: #dc3545;
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
            width: 45%;
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
        .total-expenses {
            color: #dc3545;
        }
        .total-tax {
            color: #007bff;
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
        .categories {
            margin-bottom: 30px;
        }
        .category-item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
        }
        .category-name {
            font-weight: bold;
        }
        .category-amount {
            text-align: right;
        }
        .billable {
            color: #28a745;
        }
        .not-billable {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Expense Report</h1>
        <p>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <h3 class="total-expenses">${{ number_format($totalExpenses, 2) }}</h3>
            <p>Total Expenses</p>
        </div>
        <div class="summary-box">
            <h3 class="total-tax">${{ number_format($totalTaxes, 2) }}</h3>
            <p>Tax Amount ({{ round(($totalTaxes / max($totalExpenses, 0.01)) * 100, 2) }}%)</p>
        </div>
    </div>

    <h2>Expense Categories</h2>
    <div class="categories">
        @forelse($expensesByCategory as $category => $amount)
        <div class="category-item">
            <div class="category-name">{{ $category }}</div>
            <div class="category-amount">${{ number_format($amount, 2) }}</div>
        </div>
        @empty
        <div class="category-item">
            <div>No expense categories found for the selected period.</div>
        </div>
        @endforelse
        <div class="category-item">
            <div class="category-name">Total</div>
            <div class="category-amount">${{ number_format($totalExpenses, 2) }}</div>
        </div>
    </div>

    <h2>Expenses ({{ count($expenses) }})</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Vendor</th>
                <th>Description</th>
                <th>Reference</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Tax</th>
                <th>Billable</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                <td>{{ $expense->category }}</td>
                <td>{{ $expense->vendor }}</td>
                <td>{{ $expense->description }}</td>
                <td>{{ $expense->reference_number }}</td>
                <td class="text-right">${{ number_format($expense->amount, 2) }}</td>
                <td class="text-right">${{ number_format($expense->tax_amount, 2) }}</td>
                <td class="{{ $expense->is_billable ? 'billable' : 'not-billable' }}">
                    {{ $expense->is_billable ? 'Yes' : 'No' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No expenses found for the selected period.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total</th>
                <th class="text-right">${{ number_format($totalExpenses, 2) }}</th>
                <th class="text-right">${{ number_format($totalTaxes, 2) }}</th>
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
