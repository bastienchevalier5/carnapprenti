<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livret extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modele() {
        return $this->belongsTo(Modele::class);
    }
}
