<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class TatuadorSolicitud extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $table = 'tatuador_solicitudes';

    protected $fillable = [
        'name',
        'studio_name',
        'city',
        'email',
        'phone',
        'message',
        'status',
    ];
}
