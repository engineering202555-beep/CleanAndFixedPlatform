<?php

namespace App\Services\ServiceCategory;

use App\Models\ServiceCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ServiceCategoryManagementService
{
    private const DISK = 'public';
    private const FOLDER = 'service-categories';

    /**
     * ما في Transaction هون بالقصد: عملية كتابة وحيدة على جدول واحد
     * (INSERT بسيط)، رفع الصورة قبلها فعملية مستقلة مالها علاقة
     * بسلامة الصف بقاعدة البيانات. الـ Transaction بتصير ضرورية
     * فعلياً بـ delete() تحت لأنها بتلمس أكتر من مصدر (صف + ملف).
     */
    public function store(array $data): ServiceCategory
    {

        $imagePath = isset($data['image']) ? $this->storeImage($data['image']) : null;

        return ServiceCategory::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'icon'       => $imagePath,
        ]);
    }

    public function update(ServiceCategory $category, array $data): ServiceCategory
    {
        $imagePath = $category->icon;

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $this->deleteImage($category->icon);
            $imagePath = $this->storeImage($data['image']);
        }

        $category->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'icon'       => $imagePath,
        ]);

        return $category;
    }

    public function delete(ServiceCategory $category): void
    {
        if ($category->serviceProviders()->exists()) {
            throw new ConflictHttpException(
                'لا يمكن حذف نوع الخدمة لوجود مقدمي خدمة منتمين إليه.'
            );
        }

        $this->deleteImage($category->icon);
        $category->delete();
    }

    private function storeImage(UploadedFile $file): string
    {
        return $file->store(self::FOLDER, self::DISK);
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
