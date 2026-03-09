// app/Models/Account.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'credits', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function addCredits(float $amount): void
    {
        $this->increment('credits', $amount);
    }

    public function deductCredits(float $amount): bool
    {
        if ($this->credits >= $amount) {
            $this->decrement('credits', $amount);
            return true;
        }
        return false;
    }
}
