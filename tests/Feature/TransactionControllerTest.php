<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_transaction_validation_error()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('transaction.store'), []);

        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'errors']);
    }

    public function test_create_page_returns_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('transaction.create'));

        $response->assertStatus(200);
        $response->assertViewIs('transaction.create');
    }
}
