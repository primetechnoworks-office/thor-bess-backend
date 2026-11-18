<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::in(array_keys(config('thor.lead_types')))],
            'status' => ['nullable', Rule::in(array_keys(config('thor.lead_statuses')))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $leads = $this->filtered($filters)->with('assignee')->latest()->paginate(15)->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'filters' => $filters,
            'typeCounts' => Lead::selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
        ]);
    }

    public function show(Lead $lead): View
    {
        return view('admin.leads.show', [
            'lead' => $lead->load('assignee'),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('thor.lead_statuses')))],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($data['status'] !== 'new' && ! $lead->contacted_at) {
            $data['contacted_at'] = now();
        }

        $lead->update($data);

        return back()->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        if ($lead->attachment_path) {
            Storage::disk('local')->delete($lead->attachment_path);
        }
        $lead->delete();

        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted.');
    }

    public function attachment(Lead $lead)
    {
        abort_unless($lead->attachment_path && Storage::disk('local')->exists($lead->attachment_path), 404);

        return Storage::disk('local')->download($lead->attachment_path, $lead->attachment_name);
    }

    /** CSV download of the current filter. */
    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['type', 'status', 'q']);
        $filename = 'thor-leads-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows ₹/– correctly
            fputcsv($out, ['ID', 'Received', 'Type', 'Status', 'Name', 'Company', 'Email', 'Phone', 'Subject', 'Message', 'Details', 'Notes']);

            $this->filtered($filters)->orderBy('id')->chunk(500, function ($leads) use ($out) {
                foreach ($leads as $lead) {
                    $details = collect($lead->details ?? [])
                        ->map(fn ($v, $k) => $k.': '.(is_array($v) ? implode(', ', $v) : $v))
                        ->implode(' | ');
                    fputcsv($out, [
                        $lead->id, $lead->created_at->format('Y-m-d H:i'), $lead->typeLabel(), $lead->statusLabel(),
                        $lead->name, $lead->company, $lead->email, $lead->phone, $lead->subject, $lead->message, $details, $lead->notes,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(array $filters)
    {
        return Lead::query()
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->search($filters['q'] ?? null);
    }
}
