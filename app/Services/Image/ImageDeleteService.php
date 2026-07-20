<?php
namespace App\Services\Image;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageDeleteService
{
    private const DISK = 'public';

    public function delete(Image $image): void
    {
        Storage::disk(self::DISK)->delete($image->path);
        $image->delete();

    }
}
