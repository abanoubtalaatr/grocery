<?php

namespace App\Support;

final class EgyptianPhoneRules
{
    /**
     * Egyptian mobile numbers: 010/011/012/015 plus 8 digits, or +20 with the same national digits (no leading 0).
     */
    public static function mobileRule(): string
    {
        return 'regex:/^(\+20(10|11|12|15)\d{8}|(010|011|012|015)\d{8})$/';
    }

    /**
     * Reject other international prefixes (+966, +1, …); Egyptian API numbers use +20 or local 010…
     */
    public static function internationalPrefixRule(): string
    {
        return 'not_regex:/^\+(?!20)/u';
    }

    public static function invalidMessage(): string
    {
        return 'Invalid phone number. Must start with 010, 011, 012, or 015 (e.g., 01012345678), or use international form +20 followed by 10 digits (e.g., +201012345678).';
    }

    public static function foreignPrefixMessage(): string
    {
        return 'Phone number must use Egyptian format: 11 digits starting with 010, 011, 012, or 015, or +20 followed by exactly 10 digits (no other country prefix).';
    }

    public static function lengthMessage(): string
    {
        return 'Phone number must be 11 digits in local format (010, 011, 012, or 015) or exactly 13 characters as +20 plus 10 digits.';
    }
}
