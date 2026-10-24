<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;

/**
 * Photos and videos for a product, uploaded several at a time from the admin
 * Add / Edit product forms. Each file becomes its own product_images row
 * (image or video), in the order it was picked.
 */
class ProductMediaService
{
    public const IMAGE_MIMES  = ['image/jpeg', 'image/png', 'image/webp'];
    public const VIDEO_MIMES  = ['video/mp4', 'video/quicktime'];
    public const IMAGE_MAX_KB = 2048;
    public const VIDEO_MAX_KB = 20480;
    public const MAX_FILES    = 20;

    public function rules(bool $required): array
    {
        return [
            'media'   => [$required ? 'required' : 'nullable', 'array', 'max:' . $this->limits()['files']],
            'media.*' => [
                'bail',
                'file',
                'mimetypes:' . implode(',', [...self::IMAGE_MIMES, ...self::VIDEO_MIMES]),
                function (string $attribute, mixed $file, \Closure $fail) {
                    if (! $file instanceof UploadedFile) {
                        return;
                    }

                    $isVideo = $this->isVideo($file);
                    $maxKb   = $isVideo ? $this->limits()['videoKb'] : $this->limits()['imageKb'];

                    if ($file->getSize() > $maxKb * 1024) {
                        $fail("{$file->getClientOriginalName()} is too large — " . ($isVideo ? 'videos' : 'photos') . ' can be up to ' . $this->formatKb($maxKb) . '.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required'    => 'Choose at least one photo or video.',
            'media.max'         => 'You can upload up to :max files at a time.',
            'media.*.uploaded'  => 'One of the files is larger than the server accepts (' . $this->formatKb($this->limits()['fileKb']) . ' per file).',
            'media.*.mimetypes' => 'Only JPG, PNG or WEBP photos and MP4 or MOV videos can be uploaded.',
        ];
    }

    /**
     * Upload limits the form enforces before sending: the app's own limits,
     * capped by what this PHP install accepts (upload_max_filesize per file,
     * post_max_size per save, max_file_uploads files).
     */
    public function limits(): array
    {
        $fileKb = $this->iniKb('upload_max_filesize');
        $postKb = $this->iniKb('post_max_size');

        return [
            'imageKb' => min(self::IMAGE_MAX_KB, $fileKb ?: PHP_INT_MAX),
            'videoKb' => min(self::VIDEO_MAX_KB, $fileKb ?: PHP_INT_MAX),
            'fileKb'  => $fileKb ?: self::VIDEO_MAX_KB,
            // leave room for the rest of the form fields
            'totalKb' => $postKb ? max(1, $postKb - 64) : PHP_INT_MAX,
            'files'   => min(self::MAX_FILES, (int) ini_get('max_file_uploads') ?: self::MAX_FILES),
            'accept'  => implode(',', [...self::IMAGE_MIMES, ...self::VIDEO_MIMES]),
        ];
    }

    /**
     * @param UploadedFile[] $files
     */
    public function store(Product $product, array $files): int
    {
        $destination = public_path('images/products');

        foreach ($files as $file) {
            $isVideo = $this->isVideo($file);
            $name    = time() . '_' . uniqid() . '.' . strtolower($file->extension() ?: $file->getClientOriginalExtension());

            $file->move($destination, $name);

            $product->images()->create([
                'image' => $isVideo ? null : $name,
                'video' => $isVideo ? $name : null,
            ]);
        }

        return count($files);
    }

    private function isVideo(UploadedFile $file): bool
    {
        return str_starts_with((string) $file->getMimeType(), 'video/');
    }

    private function iniKb(string $key): int
    {
        $value = trim((string) ini_get($key));

        if ($value === '' || $value === '0' || $value === '-1') {
            return 0;
        }

        $number = (float) $value;

        return (int) match (strtoupper(substr($value, -1))) {
            'G'     => $number * 1024 * 1024,
            'M'     => $number * 1024,
            'K'     => $number,
            default => $number / 1024,
        };
    }

    private function formatKb(int $kb): string
    {
        return $kb >= 1024 ? rtrim(rtrim(number_format($kb / 1024, 1), '0'), '.') . ' MB' : $kb . ' KB';
    }
}
