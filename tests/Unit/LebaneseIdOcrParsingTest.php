<?php

namespace Tests\Unit;

use App\Services\OcrSpaceIdentityService;
use Tests\TestCase;

class LebaneseIdOcrParsingTest extends TestCase
{
    public function test_parse_extracted_fields_from_lebanese_id_arabic_ocr_text(): void
    {
        $rawText = <<<'TEXT'
****** Result for Image/Page 1 ******
الجمهورية اللبنانية
وزارة الداخلية
بطاقة هوية

الاسم: علي
الشهرة: حدرج
اسم الأب: صلاح
اسم الام وشهرتها: فاطمة عليان

محل الولادة: بيروت
تاريخ الولادة: ٢٧/١١/٢٠٠٤

توقيع صاحب العلاقة:

صلاح سرور
00073028821
TEXT;

        $service = new OcrSpaceIdentityService;
        $fields = $service->parseExtractedFields($rawText);

        $this->assertSame('Ali', $fields['first_name']);
        $this->assertSame('Hodroj', $fields['last_name']);
        $this->assertSame('Salah', $fields['father_name']);
        $this->assertSame('Fatima Alyan', $fields['mother_name']);
        $this->assertSame('2004-11-27', $fields['date_of_birth']);
        $this->assertSame('00073028821', $fields['national_id']);
    }
}
