<?php

namespace App\Models;

use App\ImportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'external_import_id',
        'sent_at',
        'status',
        'total_offers',
        'processed_offers',
        'error',
        'completed_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => ImportStatus::class,
    ];

    protected $attributes = [
        'status' => 'pending',
        'total_offers' => 0,
        'processed_offers' => 0,
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
