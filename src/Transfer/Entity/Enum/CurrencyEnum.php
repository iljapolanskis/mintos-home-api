<?php

namespace App\Transfer\Entity\Enum;

enum CurrencyEnum
{
    case USD;
    case EUR;
    case GBP;
    case JPY;
    case AUD;
    case CAD;
    case CHF;
    case CNY;

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
