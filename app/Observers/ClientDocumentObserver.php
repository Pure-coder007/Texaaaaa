<?php

namespace App\Observers;

use App\Mail\Admin\AdminDocumentUploadNotificationMail;
use App\Models\ClientDocument;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClientDocumentObserver
{
    /**
     * Handle the ClientDocument "created" event.
     *
     * @param  \App\Models\ClientDocument  $document
     * @return void
     */
    public function created(ClientDocument $document)
    {
        // Handle only manual document uploads here - system-generated documents
        // are handled directly in the DocumentGenerationService
        $this->handleManualDocumentUpload($document);
    }

    /**
     * Handle notifications for manual document uploads
     *
     * @param  \App\Models\ClientDocument  $document
     * @return void
     */
    protected function handleManualDocumentUpload(ClientDocument $document)
    {
        // Only notify if the document is uploaded by a client (not system-generated)
        if (!$document->is_system_generated && $document->created_by) {
            try {
                // Get admin users for notifications
                $adminUsers = User::where('role', 'admin')
                    ->where('status', 'active')
                    ->where('admin_role', '!=', null)
                    ->get();

                // Notify admins about new document
                foreach ($adminUsers as $admin) {
                    Mail::to($admin)->send(new AdminDocumentUploadNotificationMail($admin, $document));
                }
            } catch (\Exception $e) {
                Log::error('Error sending document upload notification emails: ' . $e->getMessage());
            }
        }
    }
}