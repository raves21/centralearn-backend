<?php

namespace App\Http\Services;

use App\Http\Repositories\FileAttachmentRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class FileAttachmentService
{
    private $fileAttachmentRepository;

    public function __construct(FileAttachmentRepository $fileAttachmentRepository)
    {
        $this->fileAttachmentRepository = $fileAttachmentRepository;
    }

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