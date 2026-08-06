<?php

namespace App\Services\Location;

use App\Models\ServiceArea;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ServiceAreaManagementService
{
    public function createCityWithAreas(array $data): Collection
    {
        return DB::transaction(function () use ($data) {
            return collect($data['areas'])->map(function (string $areaName) use ($data) {
                return ServiceArea::create([
                    'city' => trim($data['city']),
                    'area_name' => trim($areaName),
                ]);
            });
        });
    }

    public function addAreasToCity(array $data): Collection
    {
        return DB::transaction(function () use ($data) {
            return collect($data['areas'])->map(function (string $areaName) use ($data) {
                return ServiceArea::create([
                    'city' => trim($data['city']),
                    'area_name' => trim($areaName),
                ]);
            });
        });
    }

    /**
     * تعديل جماعي (Bulk Update) لكل الصفوف يلي عندها نفس اسم المدينة
     * القديم، بضربة استعلام واحدة، مش صف بصف.
     */
    public function renameCity(string $currentCity, string $newName): int
    {
        return ServiceArea::query()
            ->whereRaw('TRIM(city) = ?', [trim($currentCity)])
            ->update(['city' => trim($newName)]);
    }

    public function renameArea(ServiceArea $area, string $newName): ServiceArea
    {
        $area->update(['area_name' => trim($newName)]);

        return $area;
    }

    public function deleteArea(ServiceArea $area): void
    {
        $this->ensureAreaIsEmpty($area);

        $area->delete();
    }

    /**
     * حذف مدينة كاملة = حذف كل مناطقها دفعة واحدة، بشرط "كل واحدة
     * منهم بلا استثناء" خالية من بيانات مرتبطة. لو منطقة واحدة فيها
     * بيانات، نوقف العملية كاملة قبل أي حذف فعلي (فحص شامل أول،
     * تنفيذ بعدين — منطق All-or-Nothing).
     */
    public function deleteCity(string $city): void
    {
        $areas = ServiceArea::query()
            ->whereRaw('TRIM(city) = ?', [trim($city)])
            ->get();

        if ($areas->isEmpty()) {
            throw new ConflictHttpException('هذه المدينة غير موجودة أصلاً.');
        }

        foreach ($areas as $area) {
            $this->ensureAreaIsEmpty($area);
        }

        DB::transaction(function () use ($areas) {
            foreach ($areas as $area) {
                $area->delete();
            }
        });
    }

    private function ensureAreaIsEmpty(ServiceArea $area): void
    {
        $hasProviders = $area->serviceProviders()->exists();
        $hasCustomers = $area->customers()->exists();
        $hasRequests = $area->serviceRequests()->exists();

        if ($hasProviders || $hasCustomers || $hasRequests) {
            throw new ConflictHttpException(
                "لا يمكن حذف منطقة لأنها تحتوي على مقدمي خدمة أو زبائن أو طلبات مرتبطة بها."
            );
        }
    }
}
