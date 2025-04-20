<?php

namespace App\Mail\Client;

use App\Mail\BaseMail;
use App\Models\Inspection;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientInspectionNotificationMail extends BaseMail implements ShouldQueue
{
    protected $inspection;

    public function __construct(User $client, Inspection $inspection)
    {
        $data = [
            'inspection' => $inspection,
            'estate_name' => $inspection->estate->name,
            'scheduled_date' => $inspection->scheduled_date->format('F j, Y'),
            'scheduled_time' => $inspection->scheduled_time,
            'status' => $inspection->status,
        ];

        $title = 'Inspection Scheduled - ' . $inspection->estate->name;
        $body = 'Your property inspection for ' . $inspection->estate->name . ' has been scheduled.';

        parent::__construct($client, $title, $body, $data);

        $this->inspection = $inspection;
    }

    public function build()
    {
        $this->setCommonProperties();

        $clientUrl = route('filament.client.resources.inspections.index');

        return $this->view('emails.client.inspection-notification')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'inspection' => $this->inspection,
                'estateName' => $this->getProperty('estate_name'),
                'scheduledDate' => $this->getProperty('scheduled_date'),
                'scheduledTime' => $this->getProperty('scheduled_time'),
                'status' => $this->getProperty('status'),
                'clientUrl' => $clientUrl,
            ]);
    }

    protected function getIdentifier(): string
    {
        return 'client-inspection-notification';
    }
}