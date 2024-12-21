<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modele extends Model
{
    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
