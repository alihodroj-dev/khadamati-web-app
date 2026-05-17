<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class IdentityPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fileRule = [
            'nullable',
            File::types(['jpg', 'jpeg', 'png', 'pdf', 'heic', 'heif'])
                ->max(5120),
        ];

        return [
            'id_front' => array_merge(['required_without:id_front_base64'], $fileRule),
            'id_back' => array_merge(['required_without:id_back_base64'], $fileRule),
            'id_front_base64' => ['required_without:id_front', 'nullable', 'string'],
            'id_back_base64' => ['required_without:id_back', 'nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach (['id_front', 'id_back'] as $field) {
                $file = $this->file($field);

                if (! $file instanceof UploadedFile) {
                    continue;
                }

                if (! $file->isValid()) {
                    $validator->errors()->add(
                        $field,
                        $this->uploadErrorMessage($file)
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'id_front.required_without' => 'Provide id_front as a file upload or id_front_base64.',
            'id_back.required_without' => 'Provide id_back as a file upload or id_back_base64.',
            'id_front_base64.required_without' => 'Provide id_front as a file upload or id_front_base64.',
            'id_back_base64.required_without' => 'Provide id_back as a file upload or id_back_base64.',
        ];
    }

    public function resolvedIdFront(): UploadedFile
    {
        return $this->resolveIdentityFile('id_front', 'id_front_base64', 'front.jpg');
    }

    public function resolvedIdBack(): UploadedFile
    {
        return $this->resolveIdentityFile('id_back', 'id_back_base64', 'back.jpg');
    }

    protected function resolveIdentityFile(
        string $fileField,
        string $base64Field,
        string $fallbackFilename
    ): UploadedFile {
        if ($this->hasFile($fileField)) {
            return $this->file($fileField);
        }

        return $this->uploadedFileFromBase64(
            (string) $this->input($base64Field),
            $fallbackFilename,
            $fileField
        );
    }

    protected function uploadedFileFromBase64(
        string $base64,
        string $fallbackFilename,
        string $errorField
    ): UploadedFile {
        $base64 = trim($base64);

        if ($base64 === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $errorField => ['The identity image data is empty.'],
            ]);
        }

        if (str_contains($base64, ',')) {
            $base64 = substr($base64, (int) strpos($base64, ',') + 1);
        }

        $decoded = base64_decode($base64, true);

        if ($decoded === false || $decoded === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $errorField => ['The identity image data is not valid base64.'],
            ]);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'khadamati_id_');

        if ($tempPath === false) {
            throw new \RuntimeException('Unable to create a temporary file for the identity image.');
        }

        file_put_contents($tempPath, $decoded);

        $mimeType = mime_content_type($tempPath) ?: 'image/jpeg';

        return new UploadedFile(
            $tempPath,
            $fallbackFilename,
            $mimeType,
            UPLOAD_ERR_OK,
            true
        );
    }

    protected function uploadErrorMessage(UploadedFile $file): string
    {
        $message = $file->getErrorMessage();

        if ($message !== '') {
            return 'Upload failed: '.$message;
        }

        return 'Upload failed. Send multipart/form-data with image files, or use id_front_base64 / id_back_base64 in JSON.';
    }
}
