<?php

namespace App\Entity\Enum;

enum CurrencyEnum: int
{
    case USD = 0;
    case EUR = 1;
    case GBP = 2;
    case JPY = 3;
    case AUD = 4;
    case CAD = 5;
    case CHF = 6;
    case CNY = 7;

    public function getLabel(): string
    {
        return match ($this) {
            self::USD => 'United States Dollar',
            self::EUR => 'Euro',
            self::GBP => 'British Pound Sterling',
            self::JPY => 'Japanese Yen',
            self::AUD => 'Australian Dollar',
            self::CAD => 'Canadian Dollar',
            self::CHF => 'Swiss Franc',
            self::CNY => 'Chinese Yuan Renminbi',
        };
    }
}
