<?php

namespace App\Services;

use App\Entity\FileObject;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HandleFileUpload
{
    protected \App\Models\Collection $collection;

    protected Filesystem $storage;

    protected ?UploadedFile $file = null;

    public function __construct()
    {
        $this->storage = Storage::disk('public');
    }

    public function setDisk(Filesystem $disk): self
    {
        $this->storage = $disk;

        return $this;
    }

    public function forCollection(\App\Models\Collection $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function fromUpload(UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function save(): ?FileObject
    {
        if ($this->file === null) {
            throw new \RuntimeException('No file source provided.');
        }

        $uuid = Str::uuid()->toString();

        $extension = $this->file->getClientOriginalExtension();
        $mimeType = $this->file->getMimeType();
        $sourceContent = $this->file->get();

        $filename = $extension ? "{$uuid}.{$extension}" : $uuid;
        $path = "collections/{$this->collection->id}/{$filename}";
        $this->storage->put($path, $sourceContent);
        $url = 'storage/'.$path;

        $isPreviewable = Str::startsWith($mimeType, 'image/');

        $this->file = null;

        return new FileObject(
            uuid: $uuid,
            url: $url,
            is_previewable: $isPreviewable,
            mime_type: $mimeType,
            extension: $extension
        );
    }

    /**
     * @param  array<UploadedFile>  $files
     * @return array<FileObject>
     */
    public function saveMany(array $files): array
    {
        $results = [];
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $this->fromUpload($file);
            $results[] = $this->save();
        }

        return $results;
    }
}
