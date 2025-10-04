<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Mailtrap\Transport\MailtrapApiTransport;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mail::extend('mailtrap', function (array $config) {
            $transport = new MailtrapApiTransport(
                config('services.mailtrap.api_key')
            );

            // Set the endpoint to sandbox if using testing inbox
            $host = config('services.mailtrap.host', 'send.api.mailtrap.io');
            if ($host === 'sandbox.api.mailtrap.io') {
                $transport->setHost('sandbox.smtp.mailtrap.io');
            }

            return $transport;
        });
    }
}