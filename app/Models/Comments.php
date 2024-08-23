<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'post_id',
        'author_name',
        'body',
    ];

    public function post()
    {
        return $this->belongsTo(Posts::class,'post_id');
    }
}
