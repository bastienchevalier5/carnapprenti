<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\LivretFactory;
/**
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Modele|null $modele
 */
class Livret extends Model
{

    /**
     * @use HasFactory<LivretFactory>
     */
    use HasFactory;

    /**
     * Get the user associated with the livret.
     *
     * @return BelongsTo<User, Livret>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, Livret> */
        return $this->belongsTo(User::class);
    }

    /**
     * Get the modele associated with the livret.
     *
     * @return BelongsTo<Modele, Livret>
     */
    public function modele(): BelongsTo
    {
        /** @var BelongsTo<Modele, Livret> */
        return $this->belongsTo(Modele::class);
    }
}
