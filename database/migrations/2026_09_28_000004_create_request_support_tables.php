<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('uploaded_by')->constrained('users');
            $t->string('path');
            $t->string('original_name');
            $t->string('mime_type')->nullable();
            $t->unsignedBigInteger('size')->nullable();
            $t->timestamps();
        });
        Schema::create('comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->text('body');
            $t->timestamps();
        });
        Schema::create('request_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->text('description');
            $t->string('old_value')->nullable();
            $t->string('new_value')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_histories');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('attachments');
    }
};
