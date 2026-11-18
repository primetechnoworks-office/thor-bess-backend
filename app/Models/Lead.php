<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    public const TYPE_CONTACT = 'contact';
    public const TYPE_DATASHEET = 'datasheet';
    public const TYPE_QUOTE = 'quote';

    protected $fillable = [
        'type', 'status', 'name', 'company', 'email', 'phone', 'subject', 'message', 'details',
        'attachment_path', 'attachment_name', 'notes', 'assigned_to', 'ip_address', 'user_agent', 'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'contacted_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            foreach (['name', 'company', 'email', 'phone', 'subject'] as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    public function typeLabel(): string
    {
        return config('thor.lead_types.'.$this->type, ucfirst($this->type));
    }

    public function statusLabel(): string
    {
        return config('thor.lead_statuses.'.$this->status, ucfirst($this->status));
    }

    /** Badge colour class for the status pill (see thor-admin.css). */
    public function statusTone(): string
    {
        return match ($this->status) {
            'new' => 'info',
            'contacted' => 'warning',
            'qualified' => 'success',
            'spam' => 'error',
            default => 'neutral',
        };
    }

    /**
     * Label/value rows for the detail screen, CSV export and notification email.
     *
     * @return array<string, string>
     */
    public function summaryRows(): array
    {
        $d = $this->details ?? [];
        $v = fn ($value) => filled($value) ? (is_array($value) ? implode(', ', $value) : (string) $value) : '—';

        $rows = match ($this->type) {
            self::TYPE_DATASHEET => [
                'Product' => $v($this->subject),
                'Name' => $this->name, 'Company' => $v($this->company), 'Email' => $this->email, 'Phone' => $this->phone,
                'Designation' => $v($d['designation'] ?? null), 'City / Country' => $v($d['location'] ?? null),
                'Requirement' => $v($this->message),
            ],
            self::TYPE_QUOTE => [
                'Segment' => $v($this->subject), 'Application' => $v($d['application'] ?? null),
                'Energy needed' => $v($d['capacity'] ?? null), 'Peak power' => $v($d['power'] ?? null),
                'Backup time' => $v($d['backup'] ?? null), 'Charging sources' => $v($d['sources'] ?? null),
                'Quantity' => $v($d['quantity'] ?? null),
                'Name' => $this->name, 'Company' => $v($this->company), 'Email' => $this->email, 'Phone' => $this->phone,
                'Site location' => $v($d['location'] ?? null), 'Notes' => $v($this->message),
            ],
            default => [
                'Company' => $v($this->company), 'Name' => $this->name, 'Role' => $v($d['role'] ?? null),
                'Email' => $this->email, 'Phone' => $this->phone, 'Industry' => $v($this->subject),
                'Message' => $v($this->message), 'Attachment' => $v($this->attachment_name),
            ],
        };

        return $rows;
    }
}
