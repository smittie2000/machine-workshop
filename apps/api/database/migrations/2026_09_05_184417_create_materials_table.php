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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('catalogue_version_id', 64);
            $table->foreign('catalogue_version_id')->references('id')->on('catalogue_versions')->restrictOnDelete();
            $table->string('key', 64);
            $table->string('name', 120);
            $table->decimal('friction', 6, 5);
            $table->decimal('restitution', 6, 5);
            $table->unique(['catalogue_version_id', 'key']);
            $table->unique(['catalogue_version_id', 'id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
