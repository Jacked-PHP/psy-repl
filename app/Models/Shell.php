<?php

namespace App\Models;

use App\Enums\ShellMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Metable\Metable;

class Shell extends Model
{
    use HasFactory;
    use Metable;

    protected $fillable = [
        'user_id',
        'title',
        'php_binary',
        'path',
        'code',
        'output',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsDockerContextAttribute(): bool
    {
        return $this->getMeta(ShellMeta::IS_DOCKER_CONTEXT->value, false);
    }
}
