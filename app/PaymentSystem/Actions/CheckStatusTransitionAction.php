<?php

namespace App\PaymentSystem\Actions;

use App\PaymentSystem\DTO\CallbackPayloadDTO;
use App\PaymentSystem\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Log;

class CheckStatusTransitionAction
{
    public function __construct(private readonly PaymentRepository $paymentRepository) {}

    public function execute(CallbackPayloadDTO $dto): bool
    {
        $payment = $this->paymentRepository->findForCallback($dto->orderId, $dto->transactionId);

        if (!$payment) {
            Log::channel('payments')->warning('CheckStatusTransition: payment not found', [
                'order_id' => $dto->orderId,
            ]);
            return false;
        }

        $isAllowed = $payment->status->canTransitionTo($dto->status);

        if (!$isAllowed) {
            Log::channel('payments')->warning('CheckStatusTransition: transition not allowed', [
                'order_id' => $dto->orderId,
                'from'     => $payment->status->value,
                'to'       => $dto->status->value,
            ]);
        }

        return $isAllowed;
    }
}
