<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class CustomVerifyEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test if the email contains correct content
     */
    public function test_email_has_correct_content(): void
    {
        // Create a mock user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getKey')->andReturn(1);
        $user->shouldReceive('getEmailForVerification')->andReturn('test@example.com');
        $user->shouldReceive('setAttribute')
            ->with('name', 'Test User')  // Ekspektasi setter 'name'
            ->andReturnNull();  // Tidak perlu melakukan apa-apa, hanya mengembalikan null

        $user->name = 'Test User';  // Ini tidak akan memicu error


        // Create notification and mock URL facade
        $notification = Mockery::mock(CustomVerifyEmail::class)->makePartial();
        $notification->shouldReceive('verificationUrl')
            ->with($user)
            ->andReturn('https://example.com/verify-url');

        $mail = $notification->toMail($user);

        // Assert mail content
        $this->assertEquals('Verifikasi Email Anda - FinancialApp', $mail->subject);
        $this->assertEquals('Halo Test User 👋', $mail->greeting);
        $this->assertStringContainsString('Terima kasih telah mendaftar di FinancialApp.', $mail->introLines[0]);
        $this->assertEquals('Verifikasi Email Sekarang', $mail->actionText);
        $this->assertEquals('https://example.com/verify-url', $mail->actionUrl);
        $this->assertStringContainsString('Jika Anda tidak mendaftar akun, abaikan email ini.', $mail->outroLines[0]);
        $this->assertEquals('Salam hangat, Tim FinancialApp', $mail->salutation);
    }

    /**
     * Test verification URL generation
     */
    public function test_verification_url_generation(): void
    {
        // Mock the URL facade
        URL::shouldReceive('temporarySignedRoute')
            ->once()
            ->withArgs(function ($route, $expiration, $parameters) {
                return $route === 'verification.verify' &&
                    $parameters['id'] === 1 &&
                    $parameters['hash'] === sha1('test@example.com');
            })
            ->andReturn('https://example.com/verification-link');

        // Create a mock user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getKey')->andReturn(1);
        $user->shouldReceive('getEmailForVerification')->andReturn('test@example.com');

        $notification = new CustomVerifyEmail();

        // Use reflection to access protected method
        $reflectionMethod = new \ReflectionMethod(CustomVerifyEmail::class, 'verificationUrl');
        $reflectionMethod->setAccessible(true);
        $verificationUrl = $reflectionMethod->invoke($notification, $user);

        $this->assertEquals('https://example.com/verification-link', $verificationUrl);
    }
}
