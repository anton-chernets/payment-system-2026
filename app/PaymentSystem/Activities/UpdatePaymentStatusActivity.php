<?php

namespace App\PaymentSystem\Activities;

use App\PaymentSystem\Actions\UpdatePaymentStatusAction;
use App\PaymentSystem\DTO\CallbackPayloadDTO;
use Workflow\Activity;

class UpdatePaymentStatusActivity extends Activity
{
    public function execute(CallbackPayloadDTO $dto): void
    {
        app(UpdatePaymentStatusAction::class)->execute($dto);
    }
}
