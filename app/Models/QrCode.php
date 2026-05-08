<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $fillable = [
        'url',
        'color',
        'dots_type',
        'corners_square_type',
        'corners_dot_type',
    ];
}
