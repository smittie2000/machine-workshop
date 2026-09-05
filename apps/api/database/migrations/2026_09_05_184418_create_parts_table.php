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
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('catalogue_version_id', 64);
            $table->foreign('catalogue_version_id')->references('id')->on('catalogue_versions')->restrictOnDelete();
            $table->unsignedBigInteger('material_id');
            $table->foreign(['catalogue_version_id', 'material_id'])->references(['catalogue_version_id', 'id'])->on('materials')->restrictOnDelete();
            $table->string('key', 64);
            $table->string('name', 120);
            $table->string('body_type');
            $table->string('shape_type');
            $table->integer('radius_mm')->nullable();
            $table->integer('width_mm')->nullable();
            $table->integer('height_mm')->nullable();
            $table->integer('mass_g')->nullable();
            $table->string('visual_key', 100);
            $table->unique(['catalogue_version_id', 'key']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
