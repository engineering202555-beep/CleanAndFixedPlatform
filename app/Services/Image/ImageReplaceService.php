<?php
namespace App\Services\Image;

use App\Models\Image;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ImageReplaceService
{
    private const DISK = 'public';

    private const ALLOWED_TYPES = [
        ServiceRequest::class  => ['request_damage'],
        ServiceProvider::class => ['profile', 'documents'],
    ];

    public function replace(Model $imageable, UploadedFile $file, string $type): Image {

        $this->assertTypeAllowed($imageable, $type);

        $oldImage = $imageable
            ->images()
            ->where('type', $type)
            ->first();

        if ($oldImage) {

            Storage::disk(self::DISK)
                ->delete($oldImage->path);

            $oldImage->delete();
        }

        $path = $file->store(
            $this->folderFor($imageable, $type),
            self::DISK
        );

        return $imageable->images()->create([
            'path' => $path,
            'type' => $type,
        ]);
    }

    private function assertTypeAllowed(Model $imageable, string $type): void {

        $allowed = self::ALLOWED_TYPES[$imageable::class] ?? [];

        if (! in_array($type, $allowed, true)) {

            throw new InvalidArgumentException(
                "النوع {$type} غير مسموح لهذا الموديل."
            );
        }
    }

    private function folderFor(
        Model $imageable,
        string $type
    ): string {

        return strtolower(class_basename($imageable))
            . '/'
            . $imageable->getKey()
            . '/'
            . $type;
    }
}
