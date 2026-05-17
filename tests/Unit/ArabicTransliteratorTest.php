<?php

namespace Tests\Unit;

use App\Support\ArabicTransliterator;
use Tests\TestCase;

class ArabicTransliteratorTest extends TestCase
{
    public function test_transliterates_arabic_characters_character_by_character(): void
    {
        $this->assertSame('Ali', ArabicTransliterator::transliterate('علي'));
        $this->assertSame('Hodroj', ArabicTransliterator::transliterate('حدرج'));
        $this->assertSame('Salah', ArabicTransliterator::transliterate('صلاح'));
        $this->assertSame('Fatima Alyan', ArabicTransliterator::transliterate('فاطمة عليان'));
    }

    public function test_normalizes_eastern_arabic_digits(): void
    {
        $this->assertSame('27/11/2004', ArabicTransliterator::normalizeDigits('٢٧/١١/٢٠٠٤'));
        $this->assertSame('00073028821', ArabicTransliterator::normalizeDigits('٠٠٠٧٣٠٢٨٨٢١'));
    }
}
