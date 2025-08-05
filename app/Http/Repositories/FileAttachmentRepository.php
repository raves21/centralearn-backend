<?php

namespace App\Http\Repositories;

use App\Models\FileAttachment;
use Illuminate\Http\UploadedFile;

class FileAttachmentRepository extends BaseRepository
{
    public function __construct(FileAttachment $fileAttachment)
    {
        parent::__construct($fileAttachment);
    }

    public function uploadAndCreate(UploadedFile $file, string $type, string $directory = '')
    {
        $path = $file->store($directory, 'public');
        $url = asset('storage/' . $path);

        $newFile = FileAttachment::create([
            'path' => $path,
            'url' => $url,
            'type' => $type,
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension()
        ]);

        return $newFile;
    }
}
