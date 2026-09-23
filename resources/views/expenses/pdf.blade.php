<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kharchify - {{ $dateRangeText }} Statement</title>
    <style>
        /* PDF specific styles — print-safe using tables and static styling compatible with DomPDF */
        @page {
            margin: 40px 30px 45px 30px;
        }

        body {
            /* DejaVu Sans is critical for rendering the ₹ symbol correctly in DomPDF */
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            font-size: 11px;
            background: #ffffff;
        }

        /* Top Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #10b981;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
        }

        .brand-subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
            font-weight: bold;
        }

        .meta-right {
            text-align: right;
            vertical-align: top;
        }

        .badge-period {
            display: inline-block;
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        .account-holder {
            font-size: 10px;
            color: #475569;
            margin-top: 4px;
        }

        /* Summary Metric Cards */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-left: -8px;
            margin-right: -8px;
            margin-bottom: 18px;
        }

        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 8px;
            text-align: center;
            border-radius: 6px;
        }

        .summary-box.expense-box {
            border-bottom: 3px solid #ef4444;
        }

        .summary-box.income-box {
            border-bottom: 3px solid #10b981;
        }

        .summary-box.net-box {
            border-bottom: 3px solid #6366f1;
        }

        .summary-box.count-box {
            border-bottom: 3px solid #64748b;
        }

        .summary-label {
            font-size: 8.5px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }

        .summary-value {
            font-size: 13px;
            font-weight: bold;
        }

        .val-expense { color: #dc2626; }
        .val-income { color: #059669; }
        .val-net-pos { color: #4338ca; }
        .val-net-neg { color: #dc2626; }
        .val-count { color: #334155; }

        /* Statement Filter Title Banner */
        .section-banner {
            background-color: #f1f5f9;
            border-left: 3px solid #10b981;
            padding: 6px 10px;
            margin-bottom: 10px;
            font-size: 10.5px;
            font-weight: bold;
            color: #334155;
        }

        /* Transactions Ledger Table */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .ledger-table th {
            background-color: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-weight: bold;
            padding: 7px 6px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
            font-size: 9px;
            letter-spacing: 0.5px;
        }

        .ledger-table td {
            padding: 7px 6px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 10px;
            vertical-align: top;
        }

        .ledger-table tr:nth-child(even) td {
            background-color: #fafcff;
        }

        /* Type Badges */
        .badge-type-income {
            background-color: #dcfce7;
            color: #15803d;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }

        .badge-type-expense {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }

        .category-tag {
            color: #4f46e5;
            font-size: 9px;
            font-weight: bold;
        }

        .note-text {
            color: #64748b;
            font-size: 8.5px;
            margin-top: 2px;
        }

        .amount-col {
            text-align: right;
            font-weight: bold;
            font-size: 10.5px;
            white-space: nowrap;
        }

        .amt-expense {
            color: #dc2626;
        }

        .amt-income {
            color: #059669;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="brand-title">Kharchify</div>
                <div class="brand-subtitle">Systematic Personal Finance & Cash Flow Statement</div>
                <div class="account-holder">
                    Account: <b>{{ $user->name }}</b> ({{ $user->email }}) • Plan: <b>{{ ucfirst($user->plan ?? 'free') }}</b>
                </div>
            </td>
            <td class="meta-right">
                <span class="badge-period">📅 Period: {{ $dateRangeText }}</span>
                <div class="account-holder" style="margin-top: 6px;">
                    Generated on: <b>{{ now()->format('d M, Y h:i A') }}</b>
                </div>
                <div style="font-size: 8.5px; color: #94a3b8; margin-top: 2px;">
                    Report Ref: KC-{{ strtoupper(substr(md5($user->id . now()->timestamp), 0, 8)) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- 4 Summary KPI Cards for Selected Period -->
    <table class="summary-table">
        <tr>
            <td class="summary-box expense-box" width="25%">
                <span class="summary-label">Total Outflow (Spent)</span>
                <span class="summary-value val-expense">-₹{{ number_format($totalExpense, 2) }}</span>
            </td>
            <td class="summary-box income-box" width="25%">
                <span class="summary-label">Total Inflow (Earned)</span>
                <span class="summary-value val-income">+₹{{ number_format($totalIncome, 2) }}</span>
            </td>
            <td class="summary-box net-box" width="25%">
                <span class="summary-label">Net Cash Flow</span>
                <span class="summary-value {{ $netSavings >= 0 ? 'val-net-pos' : 'val-net-neg' }}">
                    {{ $netSavings >= 0 ? '+' : '-' }}₹{{ number_format(abs($netSavings), 2) }}
                </span>
            </td>
            <td class="summary-box count-box" width="25%">
                <span class="summary-label">Total Transactions</span>
                <span class="summary-value val-count">{{ $totalCount }}</span>
            </td>
        </tr>
    </table>

    <!-- Section Heading -->
    <div class="section-banner">
        Detailed Ledger • {{ $dateRangeText }} ({{ ucfirst($statementType) }} View • {{ $totalCount }} Records)
    </div>

    <!-- Unified Transactions Table -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="10%">Type</th>
                <th width="28%">Party / Source</th>
                <th width="18%">Category</th>
                <th width="16%">Method / Ref</th>
                <th width="16%" style="text-align: right;">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td>
                    <b>{{ $tx['date'] }}</b>
                    @if(!empty($tx['time']))
                    <div style="font-size: 8px; color: #94a3b8;">{{ $tx['time'] }}</div>
                    @endif
                </td>
                <td>
                    @if($tx['type'] === 'income')
                    <span class="badge-type-income">+ INFLOW</span>
                    @else
                    <span class="badge-type-expense">- OUTFLOW</span>
                    @endif
                </td>
                <td>
                    <b>{{ $tx['party'] }}</b>
                    @if(!empty($tx['note']))
                    <div class="note-text">{{ $tx['note'] }}</div>
                    @endif
                </td>
                <td>
                    <span class="category-tag">{{ $tx['category'] }}</span>
                </td>
                <td>
                    {{ $tx['payment_method'] }}
                    @if(!empty($tx['transaction_id']))
                    <div style="font-size: 8px; color: #94a3b8;">Ref: {{ $tx['transaction_id'] }}</div>
                    @endif
                </td>
                <td class="amount-col {{ $tx['type'] === 'income' ? 'amt-income' : 'amt-expense' }}">
                    {{ $tx['type'] === 'income' ? '+' : '-' }}₹{{ number_format($tx['amount'], 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 25px; color: #94a3b8;">
                    No transactions recorded for the selected period ({{ $dateRangeText }}).
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Generated securely by <b>Kharchify</b> • kharchify.site • Systematic Expense Management & Cash Flow Tracker
    </div>

</body>
</html>
