<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LeadReceived;
use App\Models\Lead;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Receives the three website forms. Validation mirrors the Next.js API routes
 * (frontend/app/api/{contact,datasheet,quote}/route.ts) so the site can POST here directly.
 */
class LeadController extends Controller
{
    private const PHONE = 'regex:/^[0-9 +()\-]{7,20}$/';
    private const EMAIL = 'regex:/^[^\s@<>]+@[^\s@<>]+\.[^\s@<>]{2,}$/';

    private const MESSAGES = [
        'required' => 'Please fill in all required fields.',
        'max' => 'One of the fields is too long.',
        'email.regex' => 'Please enter a valid email address.',
        'phone.regex' => 'Please enter a valid phone number.',
        'attachment.max' => 'Please keep attachments under 4 MB.',
        'attachment.extensions' => "That file type isn't supported. Please upload PDF, Word, Excel, image, DWG or ZIP.",
        'product.in' => 'Please select a product.',
        'segment.in' => 'Please choose a segment.',
        'application.in' => 'Please choose an application from the list.',
    ];

    /** POST /api/leads/contact — multipart, optional attachment. */
    public function contact(Request $request): JsonResponse
    {
        if ($this->isBot($request)) {
            return $this->ok();
        }

        $data = $this->check($request, [
            'organization' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:80'],
            'role' => ['nullable', 'string', 'max:80'],
            'email' => ['required', 'string', 'max:120', self::EMAIL],
            'phone' => ['required', 'string', 'max:20', self::PHONE],
            'dial' => ['nullable', 'string'],
            'segment' => ['nullable', 'string'],
            'message' => ['nullable', 'string', 'max:3000'],
            'attachment' => ['nullable', 'file', 'max:4096', 'extensions:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,dwg,zip'],
        ]);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $dial = in_array($data['dial'] ?? null, config('thor.dial_codes'), true) ? $data['dial'] : '+91';
        $segment = in_array($data['segment'] ?? null, config('thor.industries'), true) ? $data['segment'] : null;

        $attachmentPath = $attachmentName = null;
        if ($file = $request->file('attachment')) {
            $attachmentName = Str::limit(preg_replace('/[^\w.\- ]+/', '_', $file->getClientOriginalName()), 120, '');
            $attachmentPath = $file->store('lead-attachments', 'local');
        }

        return $this->save($request, [
            'type' => Lead::TYPE_CONTACT,
            'name' => $data['name'],
            'company' => $data['organization'],
            'email' => $data['email'],
            'phone' => "{$dial} {$data['phone']}",
            'subject' => $segment,
            'message' => $data['message'] ?? null,
            'details' => array_filter(['role' => $data['role'] ?? null, 'dial' => $dial]),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ], config('thor.notify.contact'));
    }

    /** POST /api/leads/datasheet — JSON. */
    public function datasheet(Request $request): JsonResponse
    {
        if ($this->isBot($request)) {
            return $this->ok();
        }

        $products = Product::datasheetOptions(); // live batteries from the admin + e-Generator + container
        $data = $this->check($request, [
            'name' => ['required', 'string', 'max:80'],
            'company' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'max:120', self::EMAIL],
            'phone' => ['required', 'string', 'max:20', self::PHONE],
            'product' => ['required', Rule::in(array_keys($products))],
            'designation' => ['nullable', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        return $this->save($request, [
            'type' => Lead::TYPE_DATASHEET,
            'name' => $data['name'],
            'company' => $data['company'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subject' => $products[$data['product']],
            'message' => $data['message'] ?? null,
            'details' => array_filter([
                'product' => $data['product'],
                'designation' => $data['designation'] ?? null,
                'location' => $data['location'] ?? null,
            ]),
        ]);
    }

    /** POST /api/leads/quote — custom e-Generator requirement, JSON. */
    public function quote(Request $request): JsonResponse
    {
        if ($this->isBot($request)) {
            return $this->ok();
        }

        $data = $this->check($request, [
            'segment' => ['required', Rule::in(array_keys(config('thor.egen_segments')))],
            'application' => ['nullable', Rule::in(config('thor.egen_applications'))],
            'capacity' => ['nullable', 'string', 'max:40'],
            'power' => ['nullable', 'string', 'max:40'],
            'backup' => ['nullable', 'string', 'max:40'],
            'quantity' => ['nullable', 'string', 'max:10'],
            'sources' => ['nullable', 'array'],
            'name' => ['required', 'string', 'max:80'],
            'company' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'string', 'max:120', self::EMAIL],
            'phone' => ['required', 'string', 'max:20', self::PHONE],
            'location' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:1500'],
        ]);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $sources = array_values(array_intersect((array) ($data['sources'] ?? []), config('thor.charging_sources')));

        return $this->save($request, [
            'type' => Lead::TYPE_QUOTE,
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subject' => config('thor.egen_segments')[$data['segment']],
            'message' => $data['message'] ?? null,
            'details' => array_filter([
                'segment' => $data['segment'],
                'application' => $data['application'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'power' => $data['power'] ?? null,
                'backup' => $data['backup'] ?? null,
                'sources' => $sources,
                'quantity' => $data['quantity'] ?? null,
                'location' => $data['location'] ?? null,
            ]),
        ], config('thor.notify.quote'));
    }

    /** Hidden "website" field: humans leave it empty. Pretend success for bots. */
    private function isBot(Request $request): bool
    {
        return filled($request->input('website'));
    }

    /** Validates and returns trimmed data, or the website's { ok: false, error } response. */
    private function check(Request $request, array $rules): array|JsonResponse
    {
        $input = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $request->all());
        $validator = Validator::make($input, $rules, self::MESSAGES);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        return $validator->validated();
    }

    private function save(Request $request, array $attributes, array $recipients = []): JsonResponse
    {
        $lead = Lead::create($attributes + [
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);

        $to = $recipients ?: config('thor.notify.default');
        if ($to) {
            try {
                Mail::to($to)->send(new LeadReceived($lead));
            } catch (\Throwable $e) {
                // The lead is already saved and visible in the admin panel — don't fail the visitor.
                Log::error("Lead #{$lead->id} notification failed: {$e->getMessage()}");
            }
        }

        return $this->ok(['id' => $lead->id]);
    }

    private function ok(array $extra = []): JsonResponse
    {
        return response()->json(['ok' => true] + $extra);
    }

    private function fail(string $error, int $status = 422): JsonResponse
    {
        return response()->json(['ok' => false, 'error' => $error], $status);
    }
}
