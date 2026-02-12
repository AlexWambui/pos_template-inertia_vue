<?php

namespace App\Enums;

enum InventoryMovementTypes: int
{
    case PURCHASE  = 0;
    case SALE = 1;
    case ADJUSTMENT = 2;
    case RETURN = 3;
    case WASTE = 4;
}
