<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class QrCode extends Model
{
    protected $fillable = [
        'url',
        'color',
        'dots_type',
        'corners_square_type',
        'corners_dot_type',
    ];
}
