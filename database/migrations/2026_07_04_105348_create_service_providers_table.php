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
            $table->decimal('rating',3,2)->default(0);  //مثل 4.7
            $table->time('working_from');
            $table->time('working_to');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('rejection_reason')->nullable();
            $table->string('block_reason')->nullable();
            $table->boolean('do_not_disturb')->default(false);
            $table->enum('account_status', ['pending','active','blocked','rejected'])->default('pending');
            $table->enum('availability_status', ['busy', 'available', 'offline'])->default('available');
            $table->timestamp('blocked_until')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Supply/Demand + Provider Distribution: نفس التركيبة المطلوبة بكل الاستعلامين
            $table->index(['service_area_id', 'service_category_id', 'account_status'], 'sp_area_category_status_idx');
            if (! $this->indexExists('service_providers', 'service_category_id')) {
                $table->index('service_category_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }

    private function indexExists(string $table, string $column): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $index) {
            if (in_array($column, $index['columns'], true)) {
                return true;
            }
        }

        return false;
    }
};
