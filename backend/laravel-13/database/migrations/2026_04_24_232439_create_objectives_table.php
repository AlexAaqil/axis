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
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('label');
            $table->text('description')->nullable();

            $table->string('color')->nullable()->default('#3B82F6');
            $table->string('icon')->nullable()->default('list');

            $table->integer('sort_order')->default(0);
            $table->unsignedTinyInteger('priority')->default(0);
            $table->unsignedTinyInteger('status')->default(0);

            $table->timestamp('due_date')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('quarter_id')->constrained('quarters')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['quarter_id', 'status']);
            $table->index(['status', 'quarter_id']);
            $table->index(['quarter_id', 'sort_order']);
            $table->index(['status', 'due_date']);
            $table->index(['priority', 'status']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objectives');
    }
};
