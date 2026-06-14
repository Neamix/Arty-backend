<?php

namespace Modules\ProjectManagment\Enums;

enum FieldType: string
{
    case Text = 'text';
    case Select = 'select';
    case Map = 'map';
    case Date = 'date';
    case DateTime = 'datetime';
}
