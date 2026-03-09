// app/Models/Class.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Class extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'teacher', 'schedule', 'room', 'is_active'];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }
}
