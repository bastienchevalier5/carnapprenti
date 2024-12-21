<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    use HasFactory;

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'groupe_matiere');
    }
}
