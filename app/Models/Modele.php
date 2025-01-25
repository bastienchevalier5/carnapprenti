<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Groupe|null $groupe
 * @property Site|null $site
 */
class Modele extends Model
{
    /**
     * @use HasFactory<CompteRenduFactory>
     */
    use HasFactory;

    /**
     * Get the related Groupe model.
     *
     * @return BelongsTo<Groupe, Modele>
     */
    public function groupe(): BelongsTo
    {
        /** @var BelongsTo<Groupe, Modele> */
        return $this->belongsTo(Groupe::class);
    }

    /**
     * Get the related Site model.
     *
     * @return BelongsTo<Site, Modele>
     */
    public function site(): BelongsTo
    {
        /** @var BelongsTo<Site, Modele> */
        return $this->belongsTo(Site::class);
    }
}
