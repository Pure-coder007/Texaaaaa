<?php

namespace App\Mail\Pbo;

use App\Mail\BaseMail;
use App\Models\Inspection;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class PboInspectionNotificationMail extends BaseMail implements ShouldQueue
{
    protected $inspection;

    public function __construct(User $pbo, Inspection $inspection)
    {
        $data = [
            'inspection' => $inspection,
            'client_name' => $inspection->client->name,
            'estate_name' => $inspection->estate->name,
            'scheduled_date' => $inspection->scheduled_date->format('F j, Y'),
            'scheduled_time' => $inspection->scheduled_time,
            'status' => $inspection->status,
        ];

        $title = 'New Inspection Scheduled - ' . $inspection->estate->name;
        $body = 'A client has scheduled an inspection for a property in ' . $inspection->estate->name;

        parent::__construct($pbo, $title, $body, $data);

        $this->inspection = $inspection;
    }

    public function build()
    {
        $this->setCommonProperties();

        $pboUrl = route('filament.pbo.resources.inspections.index');

        return $this->view('emails.pbo.inspection-notification')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'inspection' => $this->inspection,
                'clientName' => $this->getProperty('client_name'),
                'estateName' => $this->getProperty('estate_name'),
                'scheduledDate' => $this->getProperty('scheduled_date'),
                'scheduledTime' => $this->getProperty('scheduled_time'),
                'status' => $this->getProperty('status'),
                'pboUrl' => $pboUrl,
            ]);
    }

    protected function getIdentifier(): string
    {
        return 'pbo-inspection-notification';
    }
}