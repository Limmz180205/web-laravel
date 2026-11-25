<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type', 30);
            $table->unsignedSmallInteger('year');
            $table->decimal('score', 3, 1);
            $table->json('genres');
            $table->string('status', 20);
            $table->unsignedInteger('total_units');
            $table->string('creator')->nullable();
            $table->timestamps();

            $table->unique(['type', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
