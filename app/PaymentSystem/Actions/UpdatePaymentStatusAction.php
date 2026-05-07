<?php

namespace App\PaymentSystem\Actions;

use App\PaymentSystem\DTO\CallbackPayloadDTO;
use App\PaymentSystem\Repositories\PaymentRepository;

class UpdatePaymentStatusAction
{
    public function __construct(private readonly PaymentRepository $paymentRepository) {}

    public function execute(CallbackPayloadDTO $dto): void
    {
        $payment = $this->paymentRepository->findForCallback($dto->orderId, $dto->transactionId);

        if (!$payment) {
            return;
        }

        $payment->update(['status' => $dto->status->value]);
    }
}
