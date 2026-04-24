<?php

namespace App\Support;

final class EmailValidation
{
    /** RFC 5321 maximum length for the local part (before @). */
    public const LOCAL_PART_MAX_LENGTH = 64;

    public static function invalidFormatMessage(): string
    {
        return 'Invalid email format.';
    }

    /**
     * Strict format: parser rules, printable ASCII only (rejects Arabic etc.), no trailing dot/hyphen before @,
     * domain must contain a dot, local part length at most {@see LOCAL_PART_MAX_LENGTH} (RFC 5321).
     *
     * @return list<string>
     */
    public static function formatRules(): array
    {
        $localMax = self::LOCAL_PART_MAX_LENGTH;

        return [
            'email:strict',
            'not_regex:/[-.]@/u',
            "regex:/^[!-~]{1,{$localMax}}@[!-~]+\.[!-~]+$/",
        ];
    }

    public static function domainStructureMessage(): string
    {
        return sprintf(
            'Please enter a valid email address with a proper domain extension (e.g., .com, .net). Use English (ASCII) only, up to %d characters before @, and a domain that contains a dot (e.g., name@gmail.com).',
            self::LOCAL_PART_MAX_LENGTH
        );
    }

    public static function trailingHyphenDotBeforeAtMessage(): string
    {
        return 'The part before @ must not end with a dot or hyphen. Example: name@gmail.com.';
    }
}
