<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\SendContactMessageRequest;
use App\Mail\ContactMessageMail;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    /** Mail the contact form's message to the support email saved in Admin → Settings. */
    public function send(SendContactMessageRequest $request): RedirectResponse
    {
        $supportEmail = Setting::get('support_email');

        if (! $supportEmail) {
            Log::error('Contact form message dropped: no support_email setting.');

            return back()->withInput()->with('error', 'We could not send your message right now. Please try again later.');
        }

        try {
            Mail::to($supportEmail)->send(new ContactMessageMail(
                $request->validated('name'),
                $request->validated('email'),
                $request->validated('message'),
            ));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'We could not send your message right now. Please try again later.');
        }

        return back()->with('status', 'Thanks! Your message has been sent. We will get back to you within 24 hours.');
    }
}
