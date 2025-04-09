<?php

namespace App\Entity\Enum;

enum TransactionStatusEnum: int
{
    case PENDING = 0;
    case COMPLETED = 1;
    case FAILED = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        };
    }
}
