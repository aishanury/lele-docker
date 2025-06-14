<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Transaction;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    /** @test */
    public function it_displays_dashboard_with_totals_accounts_and_recent_transactions()
    {
        $user = \App\Models\User::find(1);
        $this->assertNotNull($user, 'User with ID 1 must exist.');

        $this->actingAs($user);

        $transaction = \App\Models\Transaction::where('user_id', $user->id)
                        ->latest()->first();

        $this->assertNotNull($transaction, 'At least one transaction is required.');

        Log::info('Testing Transaction Display', [
            'id' => $transaction->id,
            'created_at' => $transaction->created_at->toDateTimeString(),
            'debit' => $transaction->debit,
            'credit' => $transaction->credit,
        ]);

        $response = $this->get('/dashboard');


        $response->assertStatus(200);

        $response->assertSee("Transaction ID: {$transaction->id}");


        $response->assertSee($transaction->created_at->diffForHumans());

        $response->assertSee(number_format($transaction->credit, 2));
        $response->assertSee(number_format($transaction->debit, 2));

        Log::info('Expected Output', [
            'expected_transaction_id' => "Transaction ID: {$transaction->id}",
            'expected_credit' => number_format($transaction->credit, 2),
            'expected_debit' => number_format($transaction->debit, 2),
        ]);


    }
}
