<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $prenom
 * @property string|null $nom
 */
class Formateur extends Model
{
    /**
     * @use HasFactory<CompteRenduFactory>
     */
    use HasFactory;
}
