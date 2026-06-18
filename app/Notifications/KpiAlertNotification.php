<?php

namespace App\Notifications;

use App\Models\KpiDefinition;
use App\Models\Problem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KpiAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Problem $problem,
        public KpiDefinition $kpi
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $locale = $notifiable->locale ?? 'de';
        $kpiName = $locale === 'de' ? $this->kpi->name_de : $this->kpi->name_en;

        return [
            'problem_id' => $this->problem->id,
            'kpi_id' => $this->kpi->id,
            'title_de' => 'Alarm: ' . $this->kpi->name_de,
            'title_en' => 'Alert: ' . $this->kpi->name_en,
            'message_de' => $this->problem->description,
            'message_en' => $this->problem->description,
            'severity' => $this->problem->severity,
            'kpi_name' => $kpiName,
        ];
    }
}
