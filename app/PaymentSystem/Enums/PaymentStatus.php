<?php

namespace App\PaymentSystem\Enums;

enum PaymentStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Success    = 'success';
    case Failed     = 'failed';

    public function canTransitionTo(self $new): bool
    {
        return match ($this) {
            self::Pending    => in_array($new, [self::Pending, self::Processing, self::Success, self::Failed]),
            self::Processing => in_array($new, [self::Processing, self::Success, self::Failed]),
            self::Success,
            self::Failed     => false,
        };
    }
}
