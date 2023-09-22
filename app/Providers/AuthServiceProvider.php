<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->greeting( env('MAIL_VERIFICATION_GREETING') )
                ->subject( env('MAIL_VERIFICATION_SUBJECT') )
                ->line( env('MAIL_VERIFICATION_MESSAGE') )
                ->salutation(" ")
                ->action(  env('MAIL_VERIFICATION_BUTTON_TEXT'), $url);
        });
    }
}
