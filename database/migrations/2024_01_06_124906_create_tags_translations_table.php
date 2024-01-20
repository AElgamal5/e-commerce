<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('language_id');
            $table->string('name');
            $table->text('description')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();

            $table->foreign('tag_id')->references('id')->on('tags');
            $table->foreign('language_id')->references('id')->on('languages');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags_translations');
    }
};
