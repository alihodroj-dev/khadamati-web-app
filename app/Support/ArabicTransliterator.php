<?php

namespace App\Support;

class ArabicTransliterator
{
    /**
     * Character-by-character Arabic → Latin transliteration for Lebanese ID OCR text.
     */
    private const CHAR_MAP = [
        'ا' => 'a', 'أ' => 'a', 'إ' => 'i', 'آ' => 'a', 'ٱ' => 'a',
        'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'j', 'ح' => 'h',
        'خ' => 'kh', 'د' => 'd', 'ذ' => 'dh', 'ر' => 'r', 'ز' => 'z',
        'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'd', 'ط' => 't',
        'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q',
        'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'ه' => 'h',
        'و' => 'w', 'ؤ' => 'o', 'ي' => 'y', 'ى' => 'a', 'ئ' => 'e',
        'ة' => 'a', 'ﻻ' => 'la', 'لا' => 'la',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    public static function transliterate(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = self::normalizeDigits($value);
        $characters = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $result = '';

        foreach ($characters as $character) {
            if (isset(self::CHAR_MAP[$character])) {
                $result .= self::CHAR_MAP[$character];

                continue;
            }

            if (preg_match('/[a-zA-Z0-9]/', $character)) {
                $result .= $character;

                continue;
            }

            if (preg_match('/[\s\/\-\.:]/u', $character)) {
                $result .= $character;
            }
        }

        $result = trim(preg_replace('/\s+/u', ' ', $result) ?? '');

        return self::applyWordFinalRules($result);
    }

    protected static function applyWordFinalRules(string $value): string
    {
        $words = preg_split('/\s+/u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $words = array_map(static function (string $word): string {
            if (str_ends_with($word, 'y')) {
                $word = substr($word, 0, -1).'i';
            }

            return ucfirst(strtolower($word));
        }, $words);

        return implode(' ', $words);
    }

    public static function normalizeDigits(string $value): string
    {
        $digitMap = [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ];

        return strtr($value, $digitMap);
    }
}
