<?php

namespace App\Helpers;

class CfuHelper
{
    public static function toInt(?string $v): ?int
    {
        if ($v === null || $v === '') return null;
        if (strtoupper($v) === 'TNTC') return PHP_INT_MAX;
        if ($v === '<1') return 0;
        if (is_numeric($v)) return (int) $v;
        return null;
    }

    public static function total(?string $b, ?string $f): ?string
    {
        if ($b === null && $f === null) return null;
        if (strtoupper((string)$b) === 'TNTC' || strtoupper((string)$f) === 'TNTC') return 'TNTC';
        $bn = ($b === '<1') ? 0 : ($b !== null && is_numeric($b) ? (int)$b : null);
        $fn = ($f === '<1') ? 0 : ($f !== null && is_numeric($f) ? (int)$f : null);
        if ($bn === null && $fn === null) return null;
        $sum = ($bn ?? 0) + ($fn ?? 0);
        if ($sum === 0 && ($b === '<1' || $f === '<1')) return '<1';
        return (string) $sum;
    }
}