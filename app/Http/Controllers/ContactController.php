<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Handle contact form submission
     */
    public function store(ContactRequest $request)
    {
        try {
            // Get the email address from environment or use default
            $recipientEmail = env('CONTACT_FORM_RECIPIENT', env('MAIL_FROM_ADDRESS', 'contact@hopo.ro'));
            
            // Send email
            Mail::to($recipientEmail)->send(new ContactFormMail($request->validated()));
            
            return response()->json([
                'success' => true,
                'message' => 'Mulțumim pentru mesaj! Te vom contacta în cel mai scurt timp.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'A apărut o eroare la trimiterea mesajului. Te rugăm să încerci din nou sau să ne contactezi direct la contact@hopo.ro'
            ], 500);
        }
    }
}
