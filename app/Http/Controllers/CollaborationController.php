<?php

namespace App\Http\Controllers;

use App\Models\CollaborationSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollaborationController extends Controller
{
    public function index(): View
    {
        return view('pages.collaboration');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'collaboration_type' => ['required', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('collaboration-attachments', 'public');
        }

        CollaborationSubmission::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'institution' => $validated['institution'] ?? null,
            'position' => $validated['position'] ?? null,
            'collaboration_type' => $validated['collaboration_type'],
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'attachment' => $attachmentPath,
            'status' => 'new',
        ]);

        return redirect()
            ->route('collaboration.index')
            ->with('status', 'Pengajuan kolaborasi berhasil dikirim. Tim Edulaw akan meninjau dan menindaklanjuti pengajuan Anda.');
    }
}