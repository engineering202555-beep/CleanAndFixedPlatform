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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_area_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'processing','accepting','completed','rejected','cancel_by_customer','cancel_by_provider','cancel_by_system'])->default('pending');
            $table->text('description')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->decimal('latitude_x', 10, 7);
            $table->decimal('longitude_y', 10, 7);
            $table->boolean('is_urgent')->default(false);
            $table->unsignedSmallInteger('duration_in_minutes')->default(30);  //مدة الطلب نصف ساعة
            $table->timestamp('expires_at');   // هذه المدة هي = لحظة انشاء الطلب + 30
            $table->unsignedTinyInteger('search_level')->default(1);// عداد للبحث اول مرة خلال نفس المنطقة عن مقدم خدمة, الثانية لكل المناطق
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
