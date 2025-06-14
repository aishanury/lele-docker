<?php

namespace App\Http\Controllers;

use App\Exports\Report;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function export()
    {
        $transactions = Transaction::with(['chartOfAccount.category'])
            ->join('chart_of_accounts as coa', 'transactions.coa_code', '=', 'coa.code')
            ->join('categories as cat', 'coa.category_name', '=', 'cat.name')
            ->where('transactions.user_id', '=', Auth::id())
            ->selectRaw("
        DATE(transactions.date) as transaction_date,
        cat.name as category, 
        cat.type as category_type,
        SUM(transactions.debit) as total_debit,
        SUM(transactions.credit) as total_credit,
        SUM(transactions.debit) + SUM(transactions.credit) as amount
    ")
            ->groupBy('transaction_date', 'cat.name', 'cat.type')
            ->orderBy('transaction_date')
            ->get();

        $groupedTransactions = $transactions->groupBy('transaction_date')->map(function ($items) {
            return $items->pluck('amount', 'category');
        });

        $categories = $transactions->groupBy('category_type')->map(function ($items) {
            return $items->pluck('category')->unique()->values();
        });

        $report = new Report(compact('groupedTransactions', 'categories'));
        return $report->download('report.xlsx');
    }
}
