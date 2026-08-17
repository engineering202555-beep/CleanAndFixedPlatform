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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 8, 2)->nullable();//في حالة الاعطال غير محددة
            $table->unsignedSmallInteger('estimated_duration')->nullable(); //المدة التنفيذية
            $table->enum('status', ['pending', 'rejected','accepted'])->default('pending');
            $table->text('notes')->nullable(); // تتضمن هذخ الملاحظة المدة الزمنية ل
            $table->unsignedSmallInteger('duration_in_minutes')->default(60);  //مدة العرض هي نصف ساعة بحالة pending
            $table->dateTime('expires_at');   // هذه المدة هي = لحظة انشاء العرض + 30
            $table->timestamps();

            $table->index(['service_provider_id','service_request_id'], 'offers_provider_request_idx');
            // Price Intelligence: كل استعلامات الأسعار بتفلتر status+price سوا
            $table->index(['status', 'price'], 'offers_status_price_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
