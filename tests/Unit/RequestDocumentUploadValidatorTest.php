<?php

namespace Tests\Unit;

use App\Support\RequestDocumentUploadValidator;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RequestDocumentUploadValidatorTest extends TestCase
{
    public function test_early_upload_errors_for_php_ini_size_limit(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'upload');

        $file = new UploadedFile(
            $path,
            'photo.jpg',
            null,
            UPLOAD_ERR_INI_SIZE,
            true
        );

        $errors = RequestDocumentUploadValidator::earlyUploadErrors($file);

        $this->assertNotNull($errors);
        $this->assertStringContainsString('upload limit', $errors['document'][0]);
    }

    public function test_early_upload_errors_when_file_missing(): void
    {
        $errors = RequestDocumentUploadValidator::earlyUploadErrors(null);

        $this->assertNotNull($errors);
        $this->assertStringContainsString('multipart/form-data', $errors['document'][0]);
    }

    public function test_accepts_heic_in_default_types(): void
    {
        $this->assertContains('heic', RequestDocumentUploadValidator::defaultAcceptedTypes());
    }
}
