<?php

namespace App\Mail\Admin;

use App\Mail\BaseMail;
use App\Models\ClientDocument;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminDocumentUploadNotificationMail extends BaseMail implements ShouldQueue
{
    protected $document;

    public function __construct(User $admin, ClientDocument $document)
    {
        $folder = $document->folder;
        $client = $folder->client;

        $data = [
            'document' => $document,
            'folder' => $folder,
            'client_name' => $client->name,
            'client_email' => $client->email,
            'document_name' => $document->name,
            'document_type' => $document->document_type,
        ];

        $title = 'New Document Uploaded - ' . $document->name;
        $body = 'A new document has been uploaded by ' . $client->name . '.';

        parent::__construct($admin, $title, $body, $data);

        $this->document = $document;
    }

    public function build()
    {
        $this->setCommonProperties();

        $adminUrl = route('filament.admin.resources.client-documents.edit', ['record' => $this->document->id]);

        return $this->view('emails.admin.document-upload-notification')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'document' => $this->document,
                'clientName' => $this->getProperty('client_name'),
                'clientEmail' => $this->getProperty('client_email'),
                'documentName' => $this->getProperty('document_name'),
                'documentType' => $this->getProperty('document_type'),
                'adminUrl' => $adminUrl,
            ]);
    }

    protected function getIdentifier(): string
    {
        return 'admin-document-upload-notification';
    }
}