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

            $table->enum('request_type', ['specific_fault','unspecified_fault'])->default('specific_fault');

            $table->enum('status', ['pending_local',   // جاري البحث محلياً
                'pending_global',   // جاري البحث ضمن نطاق المدينة كاملة
                'processing', // تم ايجاد عروض
                'accepted', // لقد اخترت عرضاً
                'completed', // تم الدفع
                'inspection_accepted', //الزبون اختار مقدم الخدمة للكشف
                'inspection_in_progress', // مقدم الخدمة يقوم بالكشف.
                'fault_detected', //تم الكشف عن العطل بنجاح
                'scheduled', // موعد التنفيذ لم يحن بعد
                'in_progress', // // بدأ مقدم الخدمة العمل
                'rejected', // لم يختار اي عرض
                'cancel_by_customer',
                'cancel_by_provider',
                'cancel_by_system'])
                ->default('pending_local');

            $table->text('description')->nullable();
            $table->timestamp('starts_at');
            $table->decimal('latitude_x', 10, 7);
            $table->decimal('longitude_y', 10, 7);
            $table->boolean('is_urgent')->default(false);
            $table->unsignedSmallInteger('duration_in_minutes')->default(30);  //مدة الطلب نصف ساعة
            $table->timestamp('expires_at');   // هذه المدة هي = لحظة انشاء الطلب + 30
            $table->unsignedTinyInteger('counter_urgent_requests_during_day')->default(0); // 2 فقط
            $table->unsignedTinyInteger('counter_cancel_by_system')->default(0); // 3 فقط
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
