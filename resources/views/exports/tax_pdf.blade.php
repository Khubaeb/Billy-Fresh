<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Report</title>
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
            color: #ffc107;
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
        .collected-tax {
            color: #007bff;
        }
        .paid-tax {
            color: #dc3545;
        }
        .net-tax-owed {
            color: #ffc107;
        }
        .net-tax-credit {
            color: #28a745;
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
        .note {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .note h3 {
            margin-top: 0;
            color: #007bff;
        }
        .note ul {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tax Report</h1>
        <p>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <h3 class="collected-tax">${{ number_format($collectedTax, 2) }}</h3>
            <p>Tax Collected</p>
        </div>
        <div class="summary-box">
            <h3 class="paid-tax">${{ number_format($paidTax, 2) }}</h3>
            <p>Tax Paid</p>
        </div>
        <div class="summary-box">
            <h3 class="{{ $netTax >= 0 ? 'net-tax-owed' : 'net-tax-credit' }}">${{ number_format(abs($netTax), 2) }}</h3>
            <p>Net Tax {{ $netTax >= 0 ? 'Owed' : 'Credit' }}</p>
        </div>
    </div>

    <div class="note">
        <h3>Tax Notes</h3>
        <p>This tax report provides a summary of sales tax collected from customers and tax paid on expenses. Use this report to help prepare your tax returns and track your tax liabilities.</p>
        <ul>
            <li>This report is for informational purposes only and should not be considered tax advice.</li>
            <li>Always consult with a qualified tax professional about your specific tax situation.</li>
            <li>If you collect sales tax, make sure to remit it to the appropriate tax authorities by the required deadlines.</li>
            <li>Keep detailed records of all tax-related transactions for audit purposes.</li>
        </ul>
    </div>

    <h2>Monthly Tax Breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-right">Tax Collected</th>
                <th class="text-right">Tax Paid</th>
                <th class="text-right">Net Tax</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monthlyTaxData as $month => $data)
            <tr>
                <td>{{ $month }}</td>
                <td class="text-right">${{ number_format($data['collected'], 2) }}</td>
                <td class="text-right">${{ number_format($data['paid'], 2) }}</td>
                <td class="text-right">
                    ${{ number_format(abs($data['net']), 2) }}
                    {{ $data['net'] >= 0 ? '(Owed)' : '(Credit)' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No tax data found for the selected period.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th class="text-right">${{ number_format($collectedTax, 2) }}</th>
                <th class="text-right">${{ number_format($paidTax, 2) }}</th>
                <th class="text-right">
                    ${{ number_format(abs($netTax), 2) }}
                    {{ $netTax >= 0 ? '(Owed)' : '(Credit)' }}
                </th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
        <p>Billy - Invoice & Expense Management System</p>
    </div>
</body>
</html>
