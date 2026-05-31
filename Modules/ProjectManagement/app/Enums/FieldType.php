<?php

namespace Modules\ProjectManagement\Enums;

enum FieldType: string
{
    // Basic Inputs
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Email = 'email';
    case Phone = 'phone';
    case Url = 'url';

    // Date & Time
    case Date = 'date';
    case Time = 'time';
    case DateTime = 'datetime';

    // Selection Inputs
    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';

    // Location Inputs
    case Country = 'country';
    case State = 'state';
    case City = 'city';
    case MapLocation = 'map_location';

    /**
     * Field types that require a list of options.
     *
     * @return array<int, self>
     */
    public static function optionable(): array
    {
        return [self::Select, self::Radio, self::Checkbox];
    }

    public function requiresOptions(): bool
    {
        return in_array($this, self::optionable(), true);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }

    /**
     * @return array<int, string>
     */
    public static function optionableValues(): array
    {
        return array_map(fn (self $type): string => $type->value, self::optionable());
    }
}
