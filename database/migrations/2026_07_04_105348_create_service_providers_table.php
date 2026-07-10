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
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_area_id')->constrained()->cascadeOnDelete();

            $table->decimal('inspection_price', 8, 2);
            $table->text('bio')->nullable();
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->boolean('is_approved')->default(false);
            $table->decimal('rating',3,2)->default(0);  //مثل 4.7
            $table->time('working_from');
            $table->time('working_to');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->enum('account_status', ['pending','active','blocked'])->default('pending');
            $table->enum('availability_status', ['busy', 'available', 'offline'])->default('available');
            $table->timestamp('blocked_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }
};
