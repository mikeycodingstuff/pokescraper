<?php

use App\Models\Pokemon;
use App\Models\Type;
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
        Schema::create('pokemon_type', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Pokemon::class)->constrained('pokemon')->cascadeOnDelete();
            $table->foreignIdFor(Type::class)->constrained('types')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pokemon_type');
    }
};
