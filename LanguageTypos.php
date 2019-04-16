<?php

namespace Cms\Utils;

/**
 *
 * @package Cms\Utils
 */
class LanguageTypos
{
    /**
     * Сопоставление русских букв к английским, которые выглядят одинаково в обоих раскладках клавиатуры
     *
     * @var string[]
     */
    private static $similarRuToEn = [
        //ru => en        //RU => EN
        "\u{0430}" => 'a', "\u{0410}" => 'A',
        /**/               "\u{0412}" => 'B',
        "\u{0435}" => 'e', "\u{0415}" => 'E',
        /**/               "\u{041a}" => 'K',
        /**/               "\u{041c}" => 'M',
        /**/               "\u{041d}" => 'H',
        "\u{043e}" => 'o', "\u{041e}" => 'O',
        "\u{0440}" => 'p', "\u{0420}" => 'P',
        "\u{0441}" => 'c', "\u{0421}" => 'C',
        /**/               "\u{0422}" => 'T',
        "\u{0443}" => 'y', "\u{0423}" => 'Y',
        "\u{0445}" => 'x', "\u{0425}" => 'X',
    ];

    /**
     * Сопоставление английских букв к русским, которые выглядят одинаково в обоих раскладках клавиатуры
     *
     * @var string[]
     */
    private static $similarEnToRu = null;

    /**
     * @var string[]
     */
    private static $similarAll = null;

    /**
     * Сопоставление QWERTY раскладки английского языка к русской
     *
     * @var string[]
     */
    private static $keyboardEnToRu = [
        #[CapsLock] off
        '`' => 'ё',
        'q' => 'й',
        'w' => 'ц',
        'e' => 'у',
        'r' => 'к',
        't' => 'е',
        'y' => 'н',
        'u' => 'г',
        'i' => 'ш',
        'o' => 'щ',
        'p' => 'з',
        '[' => 'х',
        ']' => 'ъ',
        'a' => 'ф',
        's' => 'ы',
        'd' => 'в',
        'f' => 'а',
        'g' => 'п',
        'h' => 'р',
        'j' => 'о',
        'k' => 'л',
        'l' => 'д',
        ';' => 'ж',
        '\'' => 'э',
        'z' => 'я',
        'x' => 'ч',
        'c' => 'с',
        'v' => 'м',
        'b' => 'и',
        'n' => 'т',
        'm' => 'ь',
        ',' => 'б',
        '.' => 'ю',
        '/' => '.',

        #[CapsLock] on
        '~' => 'Ё',
        '@' => '"',
        '#' => '№',
        '$' => ';',
        '^' => ':',
        '&' => '?',
        '|' => '/',
        'Q' => 'Й',
        'W' => 'Ц',
        'E' => 'У',
        'R' => 'К',
        'T' => 'Е',
        'Y' => 'Н',
        'U' => 'Г',
        'I' => 'Ш',
        'O' => 'Щ',
        'P' => 'З',
        '{' => 'Х',
        '}' => 'Ъ',
        'A' => 'Ф',
        'S' => 'Ы',
        'D' => 'В',
        'F' => 'А',
        'G' => 'П',
        'H' => 'Р',
        'J' => 'О',
        'K' => 'Л',
        'L' => 'Д',
        ':' => 'Ж',
        '"' => 'Э',
        'Z' => 'Я',
        'X' => 'Ч',
        'C' => 'С',
        'V' => 'М',
        'B' => 'И',
        'N' => 'Т',
        'M' => 'Ь',
        '<' => 'Б',
        '>' => 'Ю',
        '?' => ',',
    ];

    /**
     * Сопоставление QWERTY раскладки русского языка к английской
     *
     * @var string[]
     */
    private static $keyboardRuToEn = null;

    /**
     * Исправляет в тексте опечатки из-за неверной раскладки клавиатуры:
     *   * в словах на английском языке, ошибочно набранные русские буквы,
     *     которые похожи на английские, заменяет на английские буквы
     *   * в словах на русском языке, ошибочно набранные английские буквы,
     *     которые похожи на русские, заменяет на русские буквы
     * Алгоритм простой, быстрый и 100% надёжный (неоднозначные ситуации не обрабатываются).
     *
     * Описание алгоритма.
     * 1) Захватываем слово, в котором есть русские и английские буквы вместе.
     * 2) Смотрим на количество уникальных русских и английских букв в слове:
     *      1) Если есть уникальные русские и английских буквы одновременно,
     *         то пытаемся отделить пробелом прилипшие друг к другу слова в разной раскладке клавиатуры.
     *         Если не получилось, то возвращаем слово без изменений
     *      2) Если уникальных русских букв больше, то заменяем все английские буквы, похожие на русские, на русские.
     *      3) Если уникальных английских букв больше, то заменяем все русские буквы, похожие на английские, на английские.
     *      4) Если количество уникальных русских и английских букв одинаково, то
     *         смотрим на количество любых русских и английских букв в слове:
     *          1) Если количество русских и английских букв одинаково, то ничего не делаем.
     *          2) Если русских букв больше, то заменяем все английские буквы, похожие на русские, на русские.
     *          3) Если английских букв больше, то заменяем все русские буквы, похожие на английские, на английские.
     *
     * @param string     $str         Text in UTF-8
     * @param array|null &$replaced   Заменённые символы с количеством замен, пример:
     *                                   [
     *                                      'с' =>  [
     *                                          'char' => "c",
     *                                          'counter' => 1
     *                                       ],
     *                                   ]
     *
     * @param int         $_depth     Защита от зацикливания, служебный параметр
     *
     * @return string
     * @throws \Exception
     */
    public static function correct(string $str, array &$replaced = null, int $_depth = 0) : string
    {
        //TODO научиться исправлять в тексте ошибки с дефисами: "Во-первых"
        //захватываем слова, в которых есть русские и английские буквы вместе
        static $pattern = '/
                                [а-яА-ЯёЁ]+ [a-zA-Z]+ [a-zA-Zа-яА-ЯёЁ]*
                            |   [a-zA-Z]+ [а-яА-ЯёЁ]+ [a-zA-Zа-яА-ЯёЁ]*
                           /suxSX';

        if (strlen($str) < 3) {
            return $str; //speed improves
        }

        if (! is_array(static::$similarAll)) {
            static::$similarEnToRu = array_flip(static::$similarRuToEn);
            static::$similarAll    = static::$similarEnToRu + static::$similarRuToEn;
        }

        $str = preg_replace_callback($pattern, function (array $matches) use (&$replaced, $_depth) : string {
            $chars = preg_split('//u', $matches[0], null, PREG_SPLIT_NO_EMPTY);
            if (! is_array($chars)) {
                return $matches[0];
            }

            $charsUniqEnTotal = count(array_filter($chars, function (string $char) : bool {
                return strlen($char) === 1 && ! array_key_exists($char, static::$similarEnToRu);
            }));
            $charsUniqRuTotal = count(array_filter($chars, function (string $char) : bool {
                return strlen($char) === 2 && ! array_key_exists($char, static::$similarRuToEn);
            }));

            //самый плохой случай, когда есть уникальные русские и уникальные английские буквы вместе
            //скорее всего это прилипшие друг к другу слова на русском и английском, пытаемся разделить их
            if ($charsUniqEnTotal > 0 && $charsUniqRuTotal > 0) {
                if ($_depth > 9) {
                    return $matches[0]; //защита от зацикливания
                }
                $inserted = 0;
                $s = preg_replace(static::getSplitEnRuWordPattern(), '$0 ', $matches[0], 1, $inserted);
                if (! is_string($s)) {
                    return $matches[0];
                }
                if ($inserted) {
                    try {
                        $s = static::correct($s, $replaced, $_depth + 1);
                    } catch (\Exception $e) {
                        //падать не имеем права
                        return $matches[0];
                    }
                    return $s;
                }
            }

            $charsTotal = count($chars);

            if ($charsUniqEnTotal === $charsUniqRuTotal) {
                $charsEnTotal = count(array_filter($chars, function (string $char): bool {
                    return strlen($char) === 1;
                }));
                $charsRuTotal = $charsTotal - $charsEnTotal;
                if ($charsEnTotal === $charsRuTotal) {
                    return $matches[0];
                }
            }

            for ($i = 0; $i < $charsTotal; $i++) {
                $char = $chars[$i];
                if (array_key_exists($char, static::$similarAll)) {
                    if ($charsUniqEnTotal > $charsUniqRuTotal) {
                        if (strlen($char) === 1) {
                            continue;
                        }
                    } elseif ($charsUniqEnTotal < $charsUniqRuTotal) {
                        if (strlen($char) === 2) {
                            continue;
                        }
                    } elseif ($charsEnTotal > $charsRuTotal) {
                        if (strlen($char) === 1) {
                            continue;
                        }
                    } elseif ($charsEnTotal < $charsRuTotal) {
                        if (strlen($char) === 2) {
                            continue;
                        }
                    } else {
                        return $matches[0];
                    }
                    $chars[$i] = static::$similarAll[$char];
                } else {
                    //TODO исправлять опечатки с неверно вставленными уникальными символами из другой раскладки:
                    //wирк => цирк, цирr => цирк, салют => сал.n, опеhатор => оператор
                    continue;
                }

                if (! is_array($replaced)) {
                    continue;
                }
                if (array_key_exists($char, $replaced)) {
                    $replaced[$char]['counter']++;
                } else {
                    $replaced[$char] = [
                        'char' => $chars[$i],
                        'counter' => 1,
                    ];
                }

            }//for
            return implode('', $chars);
        }, $str);
        if (! is_string($str)) {
            $errorMessage = array_flip(get_defined_constants(true)['pcre'])[preg_last_error()];
            throw new \Exception($errorMessage);
        }

        return $str;
    }

    /**
     * Converts text from one keyboard to another.
     * Character encoding - UTF-8.
     *
     * Globalize your On Demand Business: logical keyboard layout registry index
     * Keyboard layouts for countries and regions around the world.
     * http://www-306.ibm.com/software/globalization/topics/keyboards/registry_index.jsp
     *
     * @param  string $text   Text in UTF-8
     * @param  string $input  Input keyboard layout (en, ru)
     * @param  string $output Output keyboard layout (en, ru)
     *
     * @return string
     * @throws \Exception
     */
    public static function keyboardLayoutConvert(string $text, string $input, string $output) : string
    {
        if ($input === 'en' && $output === 'ru') {
            return strtr($text, static::$keyboardEnToRu);
        }
        if (! is_array(static::$keyboardRuToEn)) {
            static::$keyboardRuToEn = array_flip(static::$keyboardEnToRu);
        }
        if ($input === 'ru' && $output === 'en') {
            return strtr($text, static::$keyboardRuToEn);
        }
        throw new \Exception("Unsupported keyboard layouts combination: input '$input' and output '$output'");
    }

    /**
     * Автоматическая конвертация раскладки из русской в анлийскую и наоборот
     * В случае, если в тексте есть и русские, и английские буквы, то возвращает исходный текст
     *
     * Сценарий использования: если по исходной поисковой фразе ничеге не найдено,
     * то конвертируем текст в другую раскладку клавиатуры и повторяем поиск.
     *
     * @param string      $text          Text in UTF-8
     * @param bool|null   &$isConverted  Возвращает TRUE, если текст был конвертирован и FALSE в противном случае
     *
     * @return string
     * @throws \Exception
     */
    public static function keyboardLayoutConvertEnRuAuto(string $text, ?bool &$isConverted = null) : string
    {
        $hasEn = preg_match('~[a-zA-Z]~sSX', $text);
        $hasRu = preg_match('~[а-яёА-ЯЁ]~suSX', $text);
        if ($hasEn === 1 && $hasRu === 0) {
            $isConverted = true;
            return static::keyboardLayoutConvert($text, 'en', 'ru');
        }
        if ($hasEn === 0 && $hasRu === 1) {
            $isConverted = true;
            return static::keyboardLayoutConvert($text, 'ru', 'en');
        }
        $isConverted = false;
        return $text;
    }

    /**
     * @param string $text
     *
     * @return string[]|null     Ассоциативный массив, где
     *                             * ключи -- это слова в другой раскладке (en/ru) из поискового запроса
     *                             * значения -- исходные слова из текста
     * @throws \Exception
     */
    public static function getWordsMap(string $text) : ?array
    {
        $words = static::getWords($text);
        //var_export($words); die; //отладка
        if (! is_array($words)) {
            return null;
        }
        if (count($words) === 0) {
            return $words;
        }
        $wordsMap = [
            static::keyboardLayoutConvertEnRuAuto($text) => $text,
        ];
        foreach ($words as $word) {
            $key = static::keyboardLayoutConvertEnRuAuto($word);
            $wordsMap[$key] = $word;
        }
        return $wordsMap;
    }

    /**
     * @param string $text
     *
     * @return string[]|null
     */
    public static function getWords(string $text) : ?array
    {
        $matches = [];
        if (preg_match_all(static::getWordsEnRuPattern(), $text, $matches, PREG_SET_ORDER) === false) {
            return null;
        }
        $words = [];
        foreach ($matches as $match) {
            $words[] = $match[0];
        }
        return $words;
    }

    /**
     * @param string $text
     *
     * @return string[]|null
     */
    public static function getChunks(string $text) : ?array
    {
        $chunks = preg_split(
            static::getWordsEnRuPattern(),
            $text,
            null,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        return is_array($chunks) ? $chunks : null;
    }

    /**
     * Переводит регистр символов строки, как в образце
     *
     * @param string $likeText  образец
     * @param string $text
     *
     * @return string
     */
    public static function convertCaseLike(string $likeText, string $text) : string
    {
        //lowercase?
        if (mb_strtolower($likeText) === $likeText) {
            return mb_strtolower($text);
        }
        //Title
        if (mb_convert_case($likeText, MB_CASE_TITLE) === $likeText) {
            return mb_convert_case($text, MB_CASE_TITLE);
        }
        //UPPERCASE?
        if (mb_strtoupper($likeText) === $likeText) {
            return mb_strtoupper($text);
        }
        //mixed?
        return $text;
    }

    /**
     * Возвращает регулярное выражение для захвата слов на русском или английском языке
     * с учётом того, что слово может быть введено в другой раскладке клавиатуры
     *
     * @return string
     */
    protected static function getWordsEnRuPattern() : string
    {
        static $pattern = null;

        if (is_string($pattern)) {
            return $pattern;
        }

        $chars = array_unique(array_merge(array_keys(static::$keyboardEnToRu), static::$keyboardEnToRu));
        sort($chars);
        $chars = preg_replace('~[a-zA-Zа-яёА-ЯЁ]+~suSX', '', implode('', $chars));
        $chars = static::pregQuoteClass($chars, '~');
        $charsEn = '(?<en>[a-zA-Z'   . $chars . ']+)';
        $charsRu = '(?<ru>[а-яёА-ЯЁ' . $chars . ']+)';
        $pattern = '~' . $charsEn . '|' . $charsRu . '~suSX';
        return $pattern;
    }

    /**
     * Возвращает регулярное выражение для разделения слова,
     * где есть уникальные русские и уникальные английские слова вместе
     *
     * @return string
     */
    protected static function getSplitEnRuWordPattern() : string
    {
        static $pattern = null;

        if (is_string($pattern)) {
            return $pattern;
        }

        $anyEn     = '[a-zA-Z]';
        $anyRu     = '[а-яА-ЯёЁ]';
        $anyEnRu   = '[a-zA-Zа-яА-ЯёЁ]';
        $similarEn = '[' . implode('', static::$similarRuToEn) . ']';
        $similarRu = '[' . implode('', static::$similarEnToRu) . ']';
        $uniqEn    = '(?:(?=[a-zA-Z])[^' . implode('', static::$similarRuToEn) . '])';
        $uniqRu    = '(?:(?=[а-яА-ЯёЁ])[^' . implode('', static::$similarEnToRu) . '])';
        $pattern = "/  $anyEn
                       $similarRu*
                       $uniqEn+
                       $similarEn*
                       (?=
                           $similarRu*
                           $uniqRu+
                           $similarEn*
                           $anyRu
                           (?! .*? $uniqEn)
                       )
                     | $anyRu
                       $similarEn*
                       $uniqRu+
                       $similarRu*
                       (?=
                           $similarEn*
                           $uniqEn+
                           $similarRu*
                           $anyEn
                           (?! .*? $uniqRu)
                       )
                    /suxSX";
        return $pattern;
    }

    /**
     * Квотирует строку для построения регулярного выражения с классом символов
     *
     * @param string      $chars
     * @param string|null $delimiter
     *
     * @return string
     */
    protected static function pregQuoteClass(string $chars, ?string $delimiter = null) : string
    {
        $quoteTable = array(
            '\\' => '\\\\',
            '^'  => '\^',
            '-'  => '\-',
            ']'  => '\]',
        );
        if ($delimiter !== null) {
            $quoteTable[$delimiter] = '\\' . $delimiter;
        }
        return strtr($chars, $quoteTable);
    }

}
