<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group', 'type'];

    protected static function booted(): void
    {
        static::saved(fn (Setting $s) => Cache::forget('settings.'.$s->key));
        static::deleted(fn (Setting $s) => Cache::forget('settings.'.$s->key));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever('settings.'.$key, function () use ($key, $default) {
            $row = static::query()->where('key', $key)->first();
            if (! $row) {
                return $default;
            }

            return match ($row->type) {
                'int', 'integer' => (int) $row->value,
                'float', 'decimal' => (float) $row->value,
                'bool', 'boolean' => filter_var($row->value, FILTER_VALIDATE_BOOL),
                'json', 'array' => json_decode((string) $row->value, true),
                default => $row->value,
            };
        });
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        $stored = is_array($value) ? json_encode($value) : (string) $value;
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type, 'group' => $group],
        );
    }
}
