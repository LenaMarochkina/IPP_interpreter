<?php

namespace IPP\Student;

class FloatHelpers
{
    /**
     * Converts a hexadecimal number string to a float number
     *
     * @param string $hex Hexadecimal number string
     * @return float Parsed float number
     */
    public static function hexdecf(string $hex): float
    {
        $exploded = explode('.', $hex);
        $integer_part = $exploded[0];
        $fractional_part = $exploded[1] ?? '';
        $integer_value = hexdec($integer_part);
        $fractional_value = $fractional_part ? intval(base_convert($fractional_part, 16, 10)) / pow(16, strlen($fractional_part)) : 0;
        return $integer_value + $fractional_value;
    }

    /**
     * Parses a string input to a float number
     *
     * @param string|null $value Input string
     * @return float|null Parsed float number or null if the input is not a valid float number
     */
    public static function parseInputFloat(?string $value): ?float
    {
        $matches = [];

        if (is_null($value)) return null;

        if (!preg_match('/^(-)?0x([\dA-Fa-f]*(\.[\dA-Fa-f]*)?)p([+-]\d*)$/m', $value, $matches)) {
            return null;
        }

        if (count($matches) !== 5) {
            return null;
        }

        $sign = $matches[1] ?? '';
        $base = $matches[2];
        $power = $matches[4];

        $decimal_value = self::hexdecf($base);

        return ($decimal_value * pow(2, intval($power))) * ($sign === '-' ? -1 : 1);
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

                    /* if we have a non-zero digit, see if we need to output a string of zeros */
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
