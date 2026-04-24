<?php

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
        Schema::create('quarters', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('label');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('year_id')->constrained('years')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['year_id', 'label']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quarters');
    }
};
