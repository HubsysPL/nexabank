<?php

namespace Modules\Core\Services;

use InvalidArgumentException;

class HrbService
{
    /**
     * Generates a full 24-digit HRB account number.
     * Note: Requires the GMP PHP extension for large number arithmetic.
     */
    public static function generate(string $bankCode, string $institutionCode, string $accountSequence): string
    {
        if (strlen($bankCode) !== 4 || strlen($institutionCode) !== 4 || strlen($accountSequence) !== 12) {
            throw new InvalidArgumentException('Invalid HRB segment length.');
        }

        $numberForChecksum = $bankCode . $institutionCode . $accountSequence . '00';

        // Use GMP for reliable modulo on large numbers
        if (! function_exists('gmp_mod')) {
            throw new \Exception('GMP extension is required for HRB checksum calculation.');
        }

        $checksumValue = 98 - gmp_mod($numberForChecksum, '97');
        $checksum = str_pad((string) $checksumValue, 2, '0', STR_PAD_LEFT);

        // The checksum in HRB standard is 4 digits, let's assume it's padded.
        $paddedChecksum = '00' . $checksum;

        return $paddedChecksum . $bankCode . $institutionCode . $accountSequence;
    }
}
