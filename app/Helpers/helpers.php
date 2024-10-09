<?php

if (!function_exists('formatNumber')) {
    /**
     * Formats a number into a readable string with 'k' or 'm' suffixes.
     *
     * @param int $number
     * @return string
     */
    function formatNumber(int $number): string
    {
        if ($number < 1000) {
            return (string) number_format($number, 0);
        } elseif ($number < 1000000) {
            return number_format($number / 1000, 2) . 'k';
        } else {
            return number_format($number / 1000000, 2) . 'm';
        }
    }
}
