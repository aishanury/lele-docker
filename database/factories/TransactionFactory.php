<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $debit = $this->faker->randomElement([1000, 0]);
        $credit = $debit === 0 ? 1000 : 0;

        $user = User::factory()->create();
        $chartOfAccount = ChartOfAccount::factory()->create(['user_id' => $user->id]);

        return [
            'user_id' => $user->id,
            'coa_code' => $chartOfAccount->code,
            'date' => $this->faker->date(),
            'description' => $this->faker->sentence,
            'debit' => $debit,
            'credit' => $credit,
        ];
    }
}
