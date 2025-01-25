<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 */
class Groupe extends Model
{
    /**
     * @use HasFactory<CompteRenduFactory>
     */
    use HasFactory;

    /**
     * Get the matieres associated with the groupe.
     *
     * @return BelongsToMany<Matiere, Groupe>
     */
    public function matieres(): BelongsToMany
    {
        /** @var BelongsToMany<Matiere, Groupe> */
        return $this->belongsToMany(Matiere::class, 'groupe_matiere');
    }
}
