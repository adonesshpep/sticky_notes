<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StickyNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'x',
        'y',
        'color',
        'rotation',
        'z_index',
        'pinned',
    ];

    protected function casts(): array
    {
        return [
            'rotation' => 'float',
            'pinned' => 'boolean',
        ];
    }
}
