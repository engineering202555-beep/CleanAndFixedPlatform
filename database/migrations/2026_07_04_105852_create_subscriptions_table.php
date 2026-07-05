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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['free', 'paid'])->default('free');
            $table->unsignedSmallInteger('requests_per_month')->default(3); //عدد الطلبات خلال فترة شهر
            $table->decimal('price', 8, 2)->default(0);
            $table->unsignedSmallInteger('duration_in_days')->default(30); // الاشتراك صالح لمدة (شهر 30)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
