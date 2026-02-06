<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Invoice Model
 * 
 * Represents a billing invoice
 * Tracks line items and payment status
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'subscription_id',
        'payment_id',
        'invoice_number',
        'stripe_invoice_id',
        'subtotal',
        'tax',
        'total',
        'currency',
        'status',
        'issued_at',
        'due_at',
        'paid_at',
        'pdf_url',
        'line_items',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'tax' => 'float',
        'total' => 'float',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
        'line_items' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the team that owns the invoice.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the subscription for this invoice.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the payment for this invoice.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'R' . number_format($this->total, 2);
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'paid' => 'green',
            'open' => 'yellow',
            'draft' => 'blue',
            'void' => 'red',
            'uncollectible' => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'open' && 
               $this->due_at && 
               $this->due_at->isPast();
    }

    /**
     * Scope query to paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope query to unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'open');
    }
}
