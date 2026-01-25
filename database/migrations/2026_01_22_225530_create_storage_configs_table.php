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
        Schema::create('storage_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->index()->constrained()->cascadeOnDelete();
            $table->string('provider')->default('local');
            $table->string('endpoint')->nullable();
            $table->string('bucket')->nullable();
            $table->string('region')->nullable();
            $table->string('access_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->boolean('s3_force_path_styling')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_configs');
    }
};
