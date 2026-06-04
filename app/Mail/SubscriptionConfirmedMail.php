<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public string       $subscriberName,
        public string       $dashboardUrl,
        public string       $userCountry = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Paiement confirmé — Plan ' . ucfirst($this->subscription->plan) . ' activé',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription_confirmed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
