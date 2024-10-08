<?php

namespace App\Models;

use App\Enums\ShellMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Metable\Metable;

class Shell extends Model
{
    use HasFactory;
    use Metable;
    use SoftDeletes;

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

    public function getIsRemoteContextAttribute(): bool
    {
        return $this->getMeta(ShellMeta::IS_REMOTE_CONTEXT->value, false);
    }
}
