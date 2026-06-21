<?php

namespace App\Domain\Reporting;

use App\Domain\Hotel\Hotel;
use App\Enums\ReportExportStatus;
use App\Models\User;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    use ScopeByHotel;

    protected $fillable = [
        'hotel_id',
        'requested_by',
        'report_type',
        'format',
        'filters',
        'status',
        'progress',
        'file_disk',
        'file_path',
        'file_name',
        'row_count',
        'error_message',
        'started_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'status' => ReportExportStatus::class,
        'progress' => 'integer',
        'row_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
