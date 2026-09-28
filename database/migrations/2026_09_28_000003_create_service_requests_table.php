<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->string('title');
            $t->text('description');
            $t->string('location');
            $t->string('priority')->default('Media');
            $t->string('status')->default('Pendiente');
            $t->timestamps();
            $t->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
