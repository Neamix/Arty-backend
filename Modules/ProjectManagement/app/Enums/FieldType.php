<?php

namespace Modules\ProjectManagement\Enums;

enum FieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Email = 'email';
    case Phone = 'phone';
    case Url = 'url';

    case Date = 'date';
    case Time = 'time';
    case DateTime = 'datetime';

    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';

    case Country = 'country';
    case State = 'state';
    case City = 'city';
    case MapLocation = 'map_location';

    public static function optionable(): array
    {
        return [self::Select, self::Radio, self::Checkbox];
    }

    public function requiresOptions(): bool
    {
        return in_array($this, self::optionable(), true);
    }

    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }

    public static function optionableValues(): array
    {
        return array_map(fn (self $type): string => $type->value, self::optionable());
    }
}
