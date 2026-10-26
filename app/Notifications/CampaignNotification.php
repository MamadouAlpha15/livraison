<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notification d'une campagne dans le compte Shopio (vendeur ou équipe), affichée sur les pages
 * Campagnes et le tableau de bord. Le push et le SMS sont envoyés à côté par CampaignNotifier.
 */
class CampaignNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $campaignId,
        public string $event,
        public string $title,
        public string $body,
        public string $url,
        public ?string $action = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'campaign',
            'event'       => $this->event,
            'campaign_id' => $this->campaignId,
            'title'       => $this->title,
            'body'        => $this->body,
            'url'         => $this->url,
            'action'      => $this->action,
        ];
    }
}
