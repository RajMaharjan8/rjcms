<?php

namespace Rjcodes\Rjcms\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Handles submissions from the example Contact page (resources/views/pages/contact.blade.php).
 */
class ContactController extends Controller
{
    /**
     * Validate and process a contact-form submission.
     */
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Wire up real delivery (Mail::to(...)) here; logged for now.
        Log::info('Contact form submission', $data);

        return back()->with('status', 'Thanks! Your message has been sent.');
    }
}
