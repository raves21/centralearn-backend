<?php

namespace App\Http\Repositories;

use App\Models\FileAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class FileAttachmentRepository extends BaseRepository
{
    public function __construct(FileAttachment $fileAttachment)
    {
        parent::__construct($fileAttachment);
    }

    public function uploadAndCreate(UploadedFile $file, string $directory = '')
    {
        $path = $file->store($directory, 'public');
        $url = asset('storage/' . $path);

        $extension = strtolower($file->getClientOriginalExtension());
        $type = match ($extension) {
            'mkv', 'mp4' => 'video',
            'pdf', 'xlsx', 'docx', 'doc' => 'document',
            'jpeg', 'jpg', 'png' => 'image',
        };

        $newFile = FileAttachment::create([
            'path' => $path,
            'url' => $url,
            'type' => $type,
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'extension' => $extension
        ]);

        return $newFile;
    }

}
