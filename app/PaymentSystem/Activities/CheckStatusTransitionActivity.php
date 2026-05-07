<?php

namespace App\PaymentSystem\Activities;

use App\PaymentSystem\Actions\CheckStatusTransitionAction;
use App\PaymentSystem\DTO\CallbackPayloadDTO;
use Workflow\Activity;

class CheckStatusTransitionActivity extends Activity
{
    public function execute(CallbackPayloadDTO $dto): bool
    {
        return app(CheckStatusTransitionAction::class)->execute($dto);
    }
}
