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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_area_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'blocked'])->default('active');
            $table->unsignedTinyInteger('counter_urgent_requests_during_day')->default(0); // 2 فقط
            $table->unsignedTinyInteger('counter_cancel_by_system')->default(0);
            $table->boolean('first_order_discount_used')->default(false);
            $table->string('block_reason')->nullable();
            $table->timestamp('blocked_until')->nullable();
             $table->string('profile_image')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
