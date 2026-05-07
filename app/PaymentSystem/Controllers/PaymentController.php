<?php

namespace App\PaymentSystem\Controllers;

use App\Http\Controllers\Controller;
use App\PaymentSystem\DTO\CreatePaymentDTO;
use App\PaymentSystem\DTO\PaymentResponseDTO;
use App\PaymentSystem\Models\Payment;
use App\PaymentSystem\PaymentProviderFactory;
use App\PaymentSystem\Repositories\CurrencyRepository;
use App\PaymentSystem\Repositories\PaymentProviderRepository;
use App\PaymentSystem\Repositories\PaymentRepository;
use App\PaymentSystem\Requests\CreatePaymentRequest;
use App\PaymentSystem\Requests\ExternalCreateRequest;
use App\PaymentSystem\Resources\PaymentResource;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentProviderFactory $factory,
        private readonly PaymentProviderRepository $providerRepository,
        private readonly CurrencyRepository $currencyRepository,
        private readonly PaymentRepository $paymentRepository,
    ) {}

    public function create(CreatePaymentRequest $request): JsonResponse
    {
        $slug     = $request->input('provider');
        $dto      = CreatePaymentDTO::fromArray($request->validated());
        $response = $this->factory->make($slug)->createPayment($dto);
        $payment  = $this->store($slug, $dto, $response);

        return (new PaymentResource($payment, $response->transactionId, $response->redirectUrl))
            ->response()
            ->setStatusCode(201);
    }

    public function createExternal(ExternalCreateRequest $request, string $provider): JsonResponse
    {
        $providerInstance = $this->factory->make($provider);
        $dto              = $providerInstance->parseExternalRequest($request->validated());
        $response         = $providerInstance->createPayment($dto);
        $this->store($provider, $dto, $response);

        return response()->json($providerInstance->formatExternalResponse($response), 201);
    }

    private function store(string $providerSlug, CreatePaymentDTO $dto, PaymentResponseDTO $response): Payment
    {
        return $this->paymentRepository->createFromDTOs(
            $dto,
            $response,
            $this->providerRepository->findBySlugOrFail($providerSlug),
            $this->currencyRepository->findByCodeOrFail($dto->currency),
        );
    }
}
