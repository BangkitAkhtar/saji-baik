<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'student_id', 'position', 'photo_path'])]
class Creator extends Model
{
    public function initials(): string
    {
        return collect(explode(' ', $this->name))->take(2)->map(fn (string $part) => mb_substr($part, 0, 1))->implode('');
    }
}
