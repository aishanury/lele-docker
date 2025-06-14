<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_custom_verification_email(): void
    {
        Notification::fake(); 

        $response = $this->post('/register', [
            'name' => 'Naufal',
            'email' => 'naufal@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'naufal@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        $response->assertRedirect(route('verification.notice'));

        Notification::assertSentTo($user, CustomVerifyEmail::class);
    }
    
}
