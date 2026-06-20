<?php

namespace App\Enums;

enum EntitlementType: string
{
    case COFFEE_BREAK = 'COFFEE_BREAK';
    case LUNCH = 'LUNCH';
    case DINNER = 'DINNER';
    case SNACK = 'SNACK';
    case WELCOME_DRINK = 'WELCOME_DRINK';
    case CUSTOM = 'CUSTOM';
}
