<?php

namespace App\Services;

class NumberToWords
{
    protected static $ones = [
        0 => '',
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
        10 => 'ten',
        11 => 'eleven',
        12 => 'twelve',
        13 => 'thirteen',
        14 => 'fourteen',
        15 => 'fifteen',
        16 => 'sixteen',
        17 => 'seventeen',
        18 => 'eighteen',
        19 => 'nineteen',
    ];

    protected static $tens = [
        0 => '',
        1 => '',
        2 => 'twenty',
        3 => 'thirty',
        4 => 'forty',
        5 => 'fifty',
        6 => 'sixty',
        7 => 'seventy',
        8 => 'eighty',
        9 => 'ninety',
    ];

    protected static $scales = [
        '', // skip empty
        'thousand',
        'million',
        'billion',
        'trillion',
        'quadrillion',
        'quintillion',
        'sextillion',
        'septillion',
        'octillion',
        'nonillion',
        'decillion'
    ];

    /**
     * Convert a number to its word representation
     *
     * @param float|int $number The number to convert
     * @return string The word representation
     */
    public static function convert($number)
    {
        if ($number == 0) {
            return 'zero';
        }

        // Check for negative numbers
        $negative = $number < 0;
        $number = abs($number);

        // Handle decimal part
        $decimalPart = '';
        if (floor($number) != $number) {
            $parts = explode('.', (string)$number);
            $number = (int)$parts[0];
            $decimal = isset($parts[1]) ? $parts[1] : '';

            if (!empty($decimal)) {
                $decimalPart = ' point ' . self::convertDecimal($decimal);
            }
        }

        $string = '';
        $fraction = $number;

        // Process groups of 3 digits
        $groups = [];
        while ($fraction > 0) {
            $groups[] = $fraction % 1000;
            $fraction = floor($fraction / 1000);
        }

        // Process each group
        for ($i = 0; $i < count($groups); $i++) {
            $groupWords = self::convertToDigits($groups[$i]);

            if (!empty($groupWords)) {
                if ($i > 0 && !empty(self::$scales[$i])) {
                    $groupWords .= ' ' . self::$scales[$i];
                }

                if (!empty($string)) {
                    $string = $groupWords . ' ' . $string;
                } else {
                    $string = $groupWords;
                }
            }
        }

        // Add negative prefix if needed
        if ($negative) {
            $string = 'negative ' . $string;
        }

        // Add decimal part if exists
        $string .= $decimalPart;

        return trim($string);
    }

    /**
     * Convert a decimal part to words
     *
     * @param string $decimal
     * @return string
     */
    private static function convertDecimal($decimal)
    {
        $result = '';
        $len = strlen($decimal);

        for ($i = 0; $i < $len; $i++) {
            $digit = (int)$decimal[$i];
            $result .= (!empty($result) ? ' ' : '') . self::$ones[$digit];
        }

        return $result;
    }

    /**
     * Convert a group of up to 3 digits to words
     *
     * @param int $number
     * @return string
     */
    private static function convertToDigits($number)
    {
        $string = '';

        // Handle hundreds
        $hundreds = floor($number / 100);
        if ($hundreds > 0) {
            $string .= self::$ones[$hundreds] . ' hundred';
            $number %= 100;

            if ($number > 0) {
                $string .= ' and ';
            }
        }

        // Handle tens and ones
        if ($number < 20) {
            // Handle 0-19
            if ($number > 0 || empty($string)) {
                $string .= self::$ones[$number];
            }
        } else {
            // Handle 20-99
            $tens = floor($number / 10);
            $ones = $number % 10;

            $string .= self::$tens[$tens];

            if ($ones > 0) {
                $string .= '-' . self::$ones[$ones];
            }
        }

        return $string;
    }
}