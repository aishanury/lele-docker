<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email Anda - FinancialApp')
            ->greeting('Halo ' . $notifiable->name . ' 👋')
            ->line('Terima kasih telah mendaftar di FinancialApp.')
            ->line('Sebelum mulai menggunakan layanan kami, silakan verifikasi email Anda dengan klik tombol di bawah ini.')
            ->action('Verifikasi Email Sekarang', $verificationUrl)
            ->line('Jika Anda tidak mendaftar akun, abaikan email ini.')
            ->salutation('Salam hangat, Tim FinancialApp');
    }

    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );
    }
}
