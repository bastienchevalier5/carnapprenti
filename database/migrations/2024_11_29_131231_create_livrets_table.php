<?php

use App\Models\Modele;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('livrets', function (Blueprint $table) {
            $table->id();
            $table->string('observation_apprenti_global')->nullable();
            $table->string('observation_admin')->nullable();
            $table->string('lien')->nullable();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Modele::class)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livrets');
    }
};
