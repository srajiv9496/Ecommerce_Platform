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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->text('banner')->nullabel();
            $table->string('type')->nullabel();
            $table->string('title')->nullabel();
            $table->string('starting_price')->nullabel();
            $table->string('btn_url')->nullabel();
            $table->integer('serial')->nullabel();
            $table->boolean('status')->nullabel();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
