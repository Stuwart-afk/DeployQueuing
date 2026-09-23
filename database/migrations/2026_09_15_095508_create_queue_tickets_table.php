<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tracking_number')->unique();
            $table->uuid('device_id');
            $table->string('mobile_number')->nullable();
            $table->string('status')->default('holding');
            $table->string('assigned_teller')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_tickets');
    }
};
