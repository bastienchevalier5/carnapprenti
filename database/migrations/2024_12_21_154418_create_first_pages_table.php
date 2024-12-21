<?php

use App\Models\Modele;
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
        Schema::create('first_pages', function (Blueprint $table) {
            $table->id();
            $table->text('logo');
            $table->text('titre');
            $table->text('image_presentation');
            $table->foreignIdFor(Modele::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('first_pages');
    }
};
