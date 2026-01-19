<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class FileAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'path',
        'url',
        'type',
        'extension',
        'name',
        'mime',
        'size'
    ];

    public function lectureMaterial()
    {
        return $this->morphOne(LectureMaterial::class, 'materialable');
    }

    public function assessmentMaterial()
    {
        return $this->morphOne(AssessmentMaterial::class, 'materialable');
    }

    public function optionBasedItemOption()
    {
        return $this->morphOne(OptionBasedItemOption::class, 'optionable');
    }

    protected static function booted()
    {
        static::deleting(function ($file) {
            Storage::disk('public')->delete($file->path);
        });
    }


    // Images
    public const MIME_JPG  = 'image/jpg';
    public const MIME_JPEG = 'image/jpeg';
    public const MIME_PNG  = 'image/png';

    // Videos
    public const MIME_MKV  = 'video/x-matroska';
    public const MIME_MP4  = 'video/mp4';

    // Documents
    public const MIME_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    public const MIME_CSV  = 'text/csv';
    public const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    public const MIME_PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
}
