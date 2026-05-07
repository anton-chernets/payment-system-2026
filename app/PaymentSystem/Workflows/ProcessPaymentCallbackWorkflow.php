<?php

namespace App\PaymentSystem\Workflows;

use App\PaymentSystem\Activities\CheckStatusTransitionActivity;
use App\PaymentSystem\Activities\UpdatePaymentStatusActivity;
use App\PaymentSystem\DTO\CallbackPayloadDTO;
use Workflow\ActivityStub;
use Workflow\Workflow;

class ProcessPaymentCallbackWorkflow extends Workflow
{
    public function execute(CallbackPayloadDTO $dto): \Generator
    {
        $isAllowed = yield ActivityStub::make(CheckStatusTransitionActivity::class, $dto);

        if ($isAllowed) {
            yield ActivityStub::make(UpdatePaymentStatusActivity::class, $dto);
        }
    }
}
