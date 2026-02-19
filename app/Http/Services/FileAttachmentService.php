<?php

namespace App\Http\Services;

use App\Http\Repositories\FileAttachmentRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class FileAttachmentService
{
    public function __construct(
        private FileAttachmentRepository $fileAttachmentRepository
    ) {}

    public static function getRandomDefaultImageUrl()
    {
        $files = Storage::disk('public')->files('default-images');
        $fileUrls = array_map(fn($file) => asset(Storage::url($file)), $files);
        return Arr::random($fileUrls);
    }

    public static function getDefaultImagesUrls()
    {
        $files = Storage::disk('public')->files('default-images');
        return array_map(fn($file) => asset(Storage::url($file)), $files);
    }
}
