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
            $table->dateTime('starts_at');
            $table->decimal('latitude_x', 10, 7);
            $table->decimal('longitude_y', 10, 7);
            $table->boolean('is_urgent')->default(false);
            $table->unsignedSmallInteger('duration_in_minutes')->default(60);  //مدة الطلب نصف ساعة
            $table->dateTime('expires_at');   // هذه المدة هي = لحظة انشاء الطلب + ساعة// 3 فقط
            $table->timestamps();

            // Composite Index
            $table->index(['created_at','request_type','service_category_id','status'], 'sr_growth_filters_idx');
            // Hot Areas + Supply/Demand: تصفية بالمنطقة+التصنيف+التاريخ سوا
            $table->index(['service_area_id', 'service_category_id', 'created_at'], 'sr_area_category_date_idx');
            // Heat Map: نطاق زمني + جلب الإحداثيات مباشرة بدون Full Scan
            $table->index(['created_at', 'latitude_x', 'longitude_y'], 'sr_density_idx');
            // "أكثر الأنواع طلباً" بتفلتر بالتصنيف + التاريخ سوا دايماً
            $table->index(['service_category_id', 'created_at'], 'sr_category_date_idx');

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
