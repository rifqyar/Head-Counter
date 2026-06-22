<?php

namespace App\Domain\QRCode;

use App\Domain\Participant\Participant;
use App\Enums\QRCredentialStatus;
use Illuminate\Database\Eloquent\Model;

class ParticipantQRCredential extends Model
{
    protected $table = 'participant_qr_credentials';

    protected $fillable = [
        'participant_id',
        'token_hash',
        'token_last_four',
        'printable_path',
        'status',
        'issued_at',
        'expires_at',
        'revoked_at',
        'revoked_by',
    ];

    protected $casts = [
        'status' => QRCredentialStatus::class,
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
