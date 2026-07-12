<?php

namespace Domain\PasswordPolicy\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * PasswordSetting model.
 *
 * Stores password policy values (expiration days, history count) as key-value pairs.
 */
class PasswordSetting extends Model
{
    use HasUuids;

    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
