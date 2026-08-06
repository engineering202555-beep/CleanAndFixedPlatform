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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();     //من ارسل الشكوى؟
            $table->foreignId('against_user_id')->nullable()->constrained('users');     //ضد من هذه الشكوى؟
            $table->foreignId('service_request_id')->nullable()->constrained();         //حصلت مع اي طلب ؟
            $table->enum('reason', [
                'provider_behavior',
                'poor_service',
                'late_arrival',
                'payment_issue',
                'technical_problem',
                'other'
            ]);
            $table->text('description');
            $table->enum('status', ['pending', 'in_review', 'resolved','rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            // Complaints by Area: الـ Join على service_request_id + التاريخ
            $table->index(['service_request_id', 'created_at'], 'complaints_request_date_idx');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
