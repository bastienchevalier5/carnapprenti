<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    /**
     * @use HasFactory<CompteRenduFactory>
     */
    use HasFactory;
    /**
     * Get the related groupes for the matiere.
     *
     * @return BelongsToMany<Groupe, Matiere>
     */
    public function groupes(): BelongsToMany
    {
        /** @var BelongsToMany<Groupe, Matiere> */
        return $this->belongsToMany(Groupe::class, 'groupe_matiere');
    }
}
