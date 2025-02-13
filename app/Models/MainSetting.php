<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'languages',
        'default_language',
    ];

    protected $casts = [
        'languages' => 'array',
    ];
}
