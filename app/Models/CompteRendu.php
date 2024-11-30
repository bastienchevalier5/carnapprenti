<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompteRendu extends Model
{
    use HasFactory;

    protected $fillable = ['id_livret', 'periode', 'activites_pro', 'observations_apprenti', 'observations_tuteur', 'observations_referent'];
}
