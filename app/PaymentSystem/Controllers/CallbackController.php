<?php

namespace App\PaymentSystem\Controllers;

use App\Http\Controllers\Controller;
use App\PaymentSystem\Contracts\PaymentProviderInterface;
use App\PaymentSystem\Repositories\PaymentRepository;
use App\PaymentSystem\Requests\CallbackRequest;
use App\PaymentSystem\Workflows\ProcessPaymentCallbackWorkflow;
use Illuminate\Http\JsonResponse;
use Workflow\WorkflowStub;

class CallbackController extends Controller
{
    public function __construct(
        private readonly PaymentProviderInterface $provider,
        private readonly PaymentRepository $paymentRepository,
    ) {}

    public function __invoke(CallbackRequest $request): JsonResponse
    {
        $callbackDto = $this->provider->handleCallback($request->validated());

        $payment = $this->paymentRepository->findForCallback($callbackDto->orderId, $callbackDto->transactionId);

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        if (!$payment->status->canTransitionTo($callbackDto->status)) {
            return response()->json([
                'error' => "Transition from {$payment->status->value} to {$callbackDto->status->value} is not allowed",
            ], 422);
        }

        $workflow = WorkflowStub::make(ProcessPaymentCallbackWorkflow::class);
        $workflow->start($callbackDto);

        return response()->json(['success' => true]);
    }
}
