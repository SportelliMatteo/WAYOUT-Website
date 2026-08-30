<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'purchase_id', 'receipt_number', 'public_token_hash', 'idempotency_key', 'locale',
        'first_name', 'last_name', 'purchase_email', 'receipt_email',
        'order_reference', 'purchase_date', 'plan', 'declaration',
        'document_versions', 'document_hashes', 'submitted_at',
        'ordinary_deadline_at', 'within_ordinary_period', 'submitted_ip_hash',
        'user_agent_hash', 'status', 'refund_status', 'receipt_email_sent_at',
        'receipt_email_failed_at',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'document_versions' => 'array',
            'document_hashes' => 'array',
            'submitted_at' => 'datetime',
            'ordinary_deadline_at' => 'datetime',
            'within_ordinary_period' => 'boolean',
            'receipt_email_sent_at' => 'datetime',
            'receipt_email_failed_at' => 'datetime',
        ];
    }
}
