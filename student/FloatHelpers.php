<?php

namespace IPP\Student;

class FloatHelpers
{
    public static function hexdecf(string $hex): float
    {
        [$integer_part, $fractional_part] = explode('.', $hex);
        $integer_value = hexdec($integer_part);
        $fractional_value = intval(base_convert($fractional_part, 16, 10));
        $fractional_value /= pow(16, strlen($fractional_part));
        $decimal_value = $integer_value + $fractional_value;

        return $decimal_value;
    }

    public static function dechexf(string $decimal): string
    {
        [$integer_part, $fractional_part] = explode('.', $decimal);
        $integer_value = dechex(intval($integer_part));
        $fractional_value = '';
        $fractional_part = floatval('0.' . $fractional_part);

        for ($i = 0; $i < 13; $i++) {
            $fractional_part *= 16;
            $integer = intval($fractional_part);
            $fractional_value .= dechex($integer);
            $fractional_part -= $integer;
        }

        $fractional_value = rtrim($fractional_value, '0');

        return $integer_value . '.' . $fractional_value;
    }

    public static function parseInputFloat(?string $value): ?float
    {
        $matches = [];

        if (is_null($value)) return null;

        if (!preg_match('/^(-)?0x([\dA-Fa-f]*\.[\dA-Fa-f]*)p([+-]\d*)$/m', $value, $matches)) {
            return null;
        }

        if (count($matches) !== 4) {
            return null;
        }

        $sign = $matches[1] ?? '';
        $base = $matches[2];
        $power = $matches[3];

        $decimal_value = self::hexdecf($base);

        return ($decimal_value * pow(2, intval($power))) * ($sign === '-' ? -1 : 1);
    }

    public static function floatToBinaryParts(float $number): array
    {
        $binary = "";
        $wholePart = intval(floor($number));
        $fractionalPart = $number - floor($number);

        for ($i = 0; $i < 21; $i++) {
            $fractionalPart *= 2;
            $binaryDigit = floor($fractionalPart);
            $binary .= $binaryDigit;
            $fractionalPart -= $binaryDigit;
        }

        return [decbin($wholePart), $binary];
    }

    /**
     * Output a float number in scientific notation
     * Uses the same format as "%a" in printf in C
     *
     * @param float $number Float number
     * @return string Float number in scientific notation
     * @see https://github.com/littlekernel/lk/blob/a070819c46c384e0b03f79bfc8fd33425bc84a1f/lib/libc/printf.c#L294
     * @copyright Copyright (c) 2008-2014 Travis Geiselbrecht
     */
    public static function floatToScienceString(float $number): string
    {
        $result = '';

        $pos = 0;
        $ui_array = unpack('Q', pack('d', $number));

        if ($ui_array === false) {
            return 'NAN';
        }

        $ui = $ui_array[1];

        $exponent = ($ui >> 52) & 0x7ff;
        $fraction = ($ui & ((1 << 52) - 1));
        $neg = !!($ui & (1 << 63));

        /* start constructing the string */
        if ($neg) {
            $result[$pos++] = '-';
        }
        /* look for special cases */
        if ($exponent == 0x7ff) {
            if ($fraction == 0) {
                /* infinity */
                $result .= 'INF';
                $pos += 3;
            } else {
                $result .= 'NAN';
                $pos += 3;
            }
        } else if ($exponent == 0) {
            if ($fraction == 0) {
                $result .= "0x0p+0";
                $pos += 7;
            } else {
                $result .= "den";
                $pos += 3;
            }
        } else {
            /* regular normalized numbers:
             * 0x1p+1
             * 0x1.0000000000001p+1
             * 0X1.FFFFFFFFFFFFFP+1023
             * 0x1.FFFFFFFFFFFFFP+1023
             */
            $exponent_signed = $exponent - 1023;

            /* implicit 1. */
            $result .= "0x1";
            $pos += 3;

            /* select the appropriate hex case table */
            $table = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'];
            $zero_count = 0;
            $output_dot = false;
            for ($i = 52 - 4; $i >= 0; $i -= 4) {
                $digit = ($fraction >> $i) & 0xf;
                if ($digit == 0) {
                    $zero_count++;
                } else {
                    /* output a . the first time we output a char */
                    if (!$output_dot) {
                        $result[$pos++] = '.';
                        $output_dot = true;
                    }

                    /* if we have a non zero digit, see if we need to output a string of zeros */
                    while ($zero_count > 0) {
                        $result[$pos++] = '0';
                        $zero_count--;
                    }
                    $result[$pos++] = $table[$digit];
                }
            }
            /* handle the exponent */
            $result[$pos++] = 'p';
            $result[$pos++] = $exponent_signed < 0 ? '-' : '+';
            $result .= abs($exponent_signed);
        }

        return $result;
    }
}
