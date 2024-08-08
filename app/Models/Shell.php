<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shell extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'php_binary',
        'path',
        'code',
        'output',
        'is_docker_context',
        'docker_container',
        'docker_workdir',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
