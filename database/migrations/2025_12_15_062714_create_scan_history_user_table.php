<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scan_history_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scan_history_id')
                ->constrained('scan_histories')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['scan_history_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_history_user');
    }
};
