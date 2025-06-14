<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;

class DashboardController extends Controller
{
    public function index() {
        $totals = \App\Models\Transaction::selectRaw('SUM(credit) as total_credit, SUM(debit) as total_debit')
                ->where('user_id', Auth::user()->id)
                ->first();
    
        $chartOfAccounts = ChartOfAccount::with('category')->where('user_id', Auth::user()->id)->get();
    
        $recentTransactions = \App\Models\Transaction::where('user_id', Auth::user()->id)
                                                     ->orderBy('id','desc')
                                                     ->take(5)
                                                     ->get();
    
        return view('dashboard', compact('chartOfAccounts', 'totals', 'recentTransactions'));
    }
    
}
