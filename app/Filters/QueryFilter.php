<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * كلاس أساسي عام لأي Query Filter بالمشروع (مقدمي خدمة، طلبات،
 * زبائن...). الهدف: تفريغ طبقة الـ Service من منطق الفلترة/الترتيب
 * بالكامل، وخلي كل Filter concrete يعرّف بس "شو مسموح" و"كيف يُطبّق".
 */
abstract class QueryFilter
{
    protected Builder $builder;

    /**
     * الفلاتر المرسلة كلها بعد resolveFilters (بما فيها القيم
     * الافتراضية المدموجة)، متاحة للـ methods الفرعية عند الحاجة
     * (مثال: sortBy() بيحتاج يقرأ sort_direction منها).
     */
    protected array $resolved = [];

    public function __construct(protected array $filters = [])
    {
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        $this->resolved = array_merge(
            $this->defaults(),
            array_intersect_key($this->filters, array_flip($this->allowedFilters()))
        );

        foreach ($this->resolved as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $method = Str::camel($key);

            // method_exists هون هو الحماية الفعلية: أي مفتاح مش
            // معرّف له method بالـ Filter الفرعي بيتجاهل بصمت، ما
            // بوصل أبداً لطبقة الاستعلام.
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this->builder;
    }

    /**
     * قيم افتراضية تُدمج قبل الفلاتر الفعلية المرسلة (مفيدة مثلاً
     * لضمان وجود ترتيب افتراضي حتى لو الطلب ما حدد شي).
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * القائمة البيضاء الوحيدة لمفاتيح الفلترة المسموحة لهذا الـ Filter.
     * أي مفتاح خارجها يُتجاهل تلقائياً حتى لو وصل بالـ Request.
     */
    abstract public function allowedFilters(): array;
}
