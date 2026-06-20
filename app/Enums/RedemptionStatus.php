<?php

namespace App\Enums;

enum RedemptionStatus: string
{
    case SUCCESS = 'SUCCESS';
    case REJECTED = 'REJECTED';
    case REVERSED = 'REVERSED';
    case OVERRIDDEN = 'OVERRIDDEN';
}
