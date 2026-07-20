<?php
namespace App\Services\Image;

use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ImageUploadService
{
    private const DISK = 'public';

    private const ALLOWED_TYPES = [
        ServiceRequest::class  => ['request_damage'],
        ServiceProvider::class => ['profile', 'documents'],
    ];

    /**
     * @param UploadedFile|UploadedFile[] $files
     */
    public function upload(Model $imageable, UploadedFile|array $files, string $type): Collection {

        $this->assertTypeAllowed($imageable, $type);

        $files = is_array($files) ? $files : [$files];

        $folder = $this->folderFor($imageable, $type);

        $created = collect();

        foreach ($files as $file) {

            $path = $file->store($folder, self::DISK);

            $created->push(
                $imageable->images()->create([
                    'path' => $path,
                    'type' => $type,
                ])
            );
        }

        return $created;
    }

    private function assertTypeAllowed(Model $imageable, string $type): void {

        $allowed = self::ALLOWED_TYPES[$imageable::class] ?? [];

        if (! in_array($type, $allowed, true)) {

            throw new InvalidArgumentException(
                "النوع {$type} غير مسموح لهذا الموديل."
            );
        }
    }

    private function folderFor(Model $imageable, string $type): string {

        return strtolower(class_basename($imageable))
            . '/'
            . $imageable->getKey()
            . '/'
            . $type;
    }
}
