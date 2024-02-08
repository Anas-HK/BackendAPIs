<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

class MailerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Mailer::class, function ($app) {
            $transport = new SmtpTransport('smtp.gmail.com');
            return new Mailer($transport);
        });
    }
}

