<?php

use App\Models\Groupe;
use App\Models\Matiere;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupeMatiereTable extends Migration
{
    public function up()
    {
        Schema::create('groupe_matiere', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Groupe::class);
            $table->foreignIdFor(Matiere::class);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('groupe_matiere');
    }
}
