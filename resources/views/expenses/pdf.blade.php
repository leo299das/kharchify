<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kharchify - Expense Report</title>
    <style>
        /* PDF specific styles — kept static/print-safe: no animation, no shadows that dompdf can't render */
        @page { margin: 50px 25px; }

        body {
            /* DejaVu Sans is critical for rendering the ₹ symbol correctly in PDFs */
            font-family: 'DejaVu Sans', sans-serif;
            color: #334155;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            font-size: 12px;
            background: #ffffff;
        }

        /* Header Section */
        .header {
            border-bottom: 3px solid #10b981;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .header h2 {
            margin: 0;
            color: #0f172a;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 22px;
        }

        .header p {
            margin: 4px 0 0 0;
            font-size: 11px;
            color: #64748b;
        }

        /* Summary Grid */
        .summary-wrapper { width: 100%; margin-bottom: 25px; }

        .summary-table { width: 100%; border-spacing: 10px; margin-left: -10px; }

        .box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-bottom: 3px solid #10b981;
            padding: 12px;
            text-align: center;
            border-radius: 8px;
        }

        .box b {
            font-size: 9px;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 5px;
        }

        .box span { font-size: 16px; font-weight: bold; color: #1e293b; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; }

        th {
            background-color: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            padding: 10px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
            font-size: 10px;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 11px;
        }

        tr:nth-child(even) td { background-color: #fafbff; }

        .amount { font-weight: bold; color: #ef4444; }

        .category-badge {
            background: #e0e7ff;
            color: #4338ca;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%; border: none; border-spacing: 0;">
            <tr>
                <td style="border: none; padding: 0;">
                    <h2>Kharchify</h2>
                    <p>Financial Statement • Generated on {{ date('d M, Y') }}</p>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: top;">
                    <div style="font-weight: bold; font-size: 14px; color: #4f46e5;">ExpTrack v3.0</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-wrapper">
        <table class="summary-table">
            <tr>
                <td class="box" width="25%">
                    <b>Total Balance</b><br>
                    <span>₹{{ number_format($totalExpense, 2) }}</span>
                </td>
                <td class="box" width="25%">
                    <b>Today</b><br>
                    <span>₹{{ number_format($todayExpense, 2) }}</span>
                </td>
                <td class="box" width="25%">
                    <b>This Month</b><br>
                    <span>₹{{ number_format($monthlyExpense, 2) }}</span>
                </td>
                <td class="box" width="25%">
                    <b>This Year</b><br>
                    <span>₹{{ number_format($yearlyExpense, 2) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="8%">ID</th>
                <th width="27%">Paid To</th>
                <th width="20%">Category</th>
                <th width="15%">Method</th>
                <th width="15%">Date</th>
                <th width="15%" style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->id }}</td>
                <td><b>{{ $expense->paid_to }}</b></td>
                <td>
                    <span class="category-badge">
                        {{ $expense->category->name ?? 'General' }}
                    </span>
                </td>
                <td>{{ $expense->payment_method }}</td>
                <td>{{ date('d-m-Y', strtotime($expense->expense_date)) }}</td>
                <td class="amount" style="text-align: right;">
                    - ₹{{ number_format($expense->amount, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © 2026 Kharchify • Systematic Expense Management by Darakshaan Sir
    </div>

</body>
</html>
