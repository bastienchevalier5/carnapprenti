<?php

use App\Models\Livret;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompteRendusTable extends Migration
{
    public function up()
    {
        Schema::create('compte_rendus', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Livret::class)->nullable(); // Clé étrangère vers la table des livrets
            $table->string('periode')->nullable();
            $table->text('activites_pro')->nullable();
            $table->text('observations_apprenti')->nullable();
            $table->text('observations_tuteur')->nullable();
            $table->text('observations_referent')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('compte_rendus');
    }
}
