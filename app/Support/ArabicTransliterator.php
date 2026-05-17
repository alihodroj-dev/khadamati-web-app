<?php

namespace App\Support;

class ArabicTransliterator
{
    /**
     * Common Lebanese ID given names / surnames (OCR-normalized Arabic → English).
     *
     * @var array<string, string>
     */
    private const ARABIC_WORD_MAP = [
        'علي' => 'Ali',
        'على' => 'Ali',
        'حسن' => 'Hassan',
        'حسين' => 'Hussein',
        'احمد' => 'Ahmad',
        'أحمد' => 'Ahmad',
        'محمد' => 'Mohammad',
        'محمود' => 'Mahmoud',
        'خالد' => 'Khaled',
        'وليد' => 'Waleed',
        'رامي' => 'Rami',
        'ريما' => 'Rima',
        'نور' => 'Nour',
        'نورا' => 'Nora',
        'ليلى' => 'Layla',
        'ليلي' => 'Layla',
        'سارة' => 'Sara',
        'مريم' => 'Miriam',
        'جورج' => 'George',
        'الياس' => 'Elias',
        'إلياس' => 'Elias',
        'بطرس' => 'Peter',
        'جوزيف' => 'Joseph',
        'جوزف' => 'Joseph',
        'انطوان' => 'Antoine',
        'أنطوان' => 'Antoine',
        'ميشال' => 'Michel',
        'ميشيل' => 'Michel',
        'كريم' => 'Karim',
        'هادي' => 'Hadi',
        'صلاح' => 'Salah',
        'فاطمة' => 'Fatima',
        'فاطمه' => 'Fatima',
        'زينب' => 'Zainab',
        'سعاد' => 'Souad',
        'نجوى' => 'Najwa',
        'حدرج' => 'Hodroj',
        'حدروج' => 'Hodroj',
        'عليان' => 'Alyan',
        'سرور' => 'Srour',
        'خوري' => 'Khoury',
        'حايك' => 'Hayek',
        'غانم' => 'Ghanem',
        'نصر' => 'Nasr',
        'حمود' => 'Hamoud',
        'شامي' => 'Chami',
        'بيروت' => 'Beirut',
    ];

    /**
     * Fixes common under-vowelled char-by-char outputs.
     *
     * @var array<string, string>
     */
    private const LATIN_WORD_CORRECTIONS = [
        'aly' => 'Ali',
        'ali' => 'Ali',
        'slah' => 'Salah',
        'salah' => 'Salah',
        'hdrj' => 'Hodroj',
        'hodrj' => 'Hodroj',
        'fatma' => 'Fatima',
        'fatima' => 'Fatima',
        'mhmd' => 'Mohammad',
        'mohammad' => 'Mohammad',
        'ahmd' => 'Ahmad',
        'ahmad' => 'Ahmad',
        'hssn' => 'Hassan',
        'hassan' => 'Hassan',
        'hsyn' => 'Hussein',
        'hussein' => 'Hussein',
        'khld' => 'Khaled',
        'khaled' => 'Khaled',
        'wld' => 'Waleed',
        'waleed' => 'Waleed',
        'srwr' => 'Srour',
        'srour' => 'Srour',
        'khwry' => 'Khoury',
        'khoury' => 'Khoury',
        'alyan' => 'Alyan',
        'fatma alyan' => 'Fatima Alyan',
    ];

    /**
     * Longest-match-first Arabic digraphs.
     *
     * @var array<string, string>
     */
    private const DIGRAPHS = [
        'لا' => 'la',
        'ش' => 'sh',
        'خ' => 'kh',
        'غ' => 'gh',
        'ث' => 'th',
        'ذ' => 'dh',
        'ظ' => 'z',
        'ض' => 'd',
        'ص' => 's',
        'ط' => 't',
        'ح' => 'h',
        'ج' => 'j',
    ];

    private const CHAR_MAP = [
        'ا' => 'a', 'أ' => 'a', 'إ' => 'i', 'آ' => 'a', 'ٱ' => 'a',
        'ب' => 'b', 'ت' => 't', 'ج' => 'j', 'د' => 'd', 'ر' => 'r',
        'ز' => 'z', 'س' => 's', 'ف' => 'f', 'ق' => 'q', 'ك' => 'k',
        'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'ه' => 'h', 'و' => 'o',
        'ؤ' => 'o', 'ي' => 'i', 'ى' => 'a', 'ئ' => 'e', 'ة' => 'a',
        'ع' => 'a', 'پ' => 'p', 'چ' => 'ch', 'ڤ' => 'v',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    public static function transliterate(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $value = self::normalizeArabic(trim($value));

        if ($value === '') {
            return '';
        }

        if (! self::containsArabic($value)) {
            return self::formatLatinWords($value);
        }

        $words = preg_split('/\s+/u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $transliterated = [];

        foreach ($words as $word) {
            $transliterated[] = self::transliterateWord($word);
        }

        return implode(' ', $transliterated);
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

    public static function normalizeArabic(string $text): string
    {
        $text = self::normalizeDigits($text);
        $text = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $text) ?? $text;
        $text = str_replace(['أ', 'إ', 'آ', 'ٱ', 'ٲ', 'ٳ', 'ٵ'], 'ا', $text);
        $text = str_replace(['ى', 'ي'], 'ي', $text);
        $text = str_replace('ة', 'ه', $text);

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    protected static function transliterateWord(string $word): string
    {
        $normalized = self::normalizeArabic($word);

        if (isset(self::ARABIC_WORD_MAP[$normalized])) {
            return self::ARABIC_WORD_MAP[$normalized];
        }

        $latin = self::transliterateArabicCharacters($normalized);
        $latin = self::applyVowelHeuristics($latin);
        $latin = self::correctLatinWord(strtolower($latin));

        return self::formatLatinWord($latin);
    }

    protected static function transliterateArabicCharacters(string $word): string
    {
        $result = '';
        $length = mb_strlen($word, 'UTF-8');
        $position = 0;

        while ($position < $length) {
            $matched = false;

            foreach (self::orderedDigraphs() as $arabic => $latin) {
                $segment = mb_substr($word, $position, mb_strlen($arabic, 'UTF-8'), 'UTF-8');

                if ($segment === $arabic) {
                    $result .= $latin;
                    $position += mb_strlen($arabic, 'UTF-8');
                    $matched = true;

                    break;
                }
            }

            if ($matched) {
                continue;
            }

            $character = mb_substr($word, $position, 1, 'UTF-8');
            $position++;

            if (isset(self::CHAR_MAP[$character])) {
                $result .= self::mapCharacter($character, $result, $word, $position);

                continue;
            }

            if (preg_match('/[a-zA-Z0-9]/', $character)) {
                $result .= $character;
            }
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    protected static function orderedDigraphs(): array
    {
        $digraphs = self::DIGRAPHS;
        uksort($digraphs, static fn (string $a, string $b): int => mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8'));

        return $digraphs;
    }

    protected static function mapCharacter(
        string $character,
        string $currentLatin,
        string $word,
        int $nextPosition
    ): string {
        if ($character === 'و') {
            $nextArabic = mb_substr($word, $nextPosition, 1, 'UTF-8');

            if ($nextArabic !== '' && ! isset(self::CHAR_MAP[$nextArabic])) {
                return 'w';
            }

            return self::needsVowelAfter($currentLatin) ? 'ou' : 'o';
        }

        if ($character === 'ي') {
            $isFinal = $nextPosition >= mb_strlen($word, 'UTF-8');

            return $isFinal ? 'i' : 'y';
        }

        return self::CHAR_MAP[$character];
    }

    protected static function needsVowelAfter(string $currentLatin): bool
    {
        if ($currentLatin === '') {
            return false;
        }

        $last = strtolower(substr($currentLatin, -1));

        return ! in_array($last, ['a', 'e', 'i', 'o', 'u'], true);
    }

    protected static function applyVowelHeuristics(string $word): string
    {
        if ($word === '' || preg_match('/[aeiou]/i', $word)) {
            return $word;
        }

        // Insert "a" after an initial consonant cluster (e.g. s-l-a-h patterns).
        if (preg_match('/^([bcdfghjklmnpqrstvwxyz])([bcdfghjklmnpqrstvwxyz]+)/i', $word, $matches)) {
            return $matches[1].'a'.substr($word, strlen($matches[1]));
        }

        return $word;
    }

    protected static function correctLatinWord(string $word): string
    {
        if (isset(self::LATIN_WORD_CORRECTIONS[$word])) {
            return strtolower(self::LATIN_WORD_CORRECTIONS[$word]);
        }

        return $word;
    }

    protected static function formatLatinWord(string $word): string
    {
        if ($word === '') {
            return '';
        }

        if (str_contains($word, '-')) {
            return self::formatLatinWords($word);
        }

        return ucfirst(strtolower($word));
    }

    protected static function formatLatinWords(string $value): string
    {
        $words = preg_split('/\s+/u', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(' ', array_map(
            static fn (string $word): string => self::formatLatinWord($word),
            $words
        ));
    }

    protected static function containsArabic(string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }
}
