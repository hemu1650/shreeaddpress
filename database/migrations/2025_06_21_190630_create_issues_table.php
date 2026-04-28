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
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flat_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('assigned_to'); // vendor_id
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');
            $table->text('description')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->string('proof_image')->nullable();
            $table->timestamps();

            $table->foreign('flat_id')->references('id')->on('flat_villas')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
