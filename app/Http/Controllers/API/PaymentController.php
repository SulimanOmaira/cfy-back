<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StripeService;

class PaymentController extends Controller
{
    use ApiResponseTrait;
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    // public function createPaymentIntent(Request $request)
    // {
    //     $validated = $request->validate([
    //         'amount' => 'required|integer|min:100',
    //         'currency' => 'sometimes|string|size:3',
    //     ]);

    //     $amount = $validated['amount'];
    //     $currency = $validated['currency'] ?? 'usd';

    //     $intent = $this->stripeService->createPaymentIntent($amount, $currency);

    //     return $this->apiResponse(
    //         ['clientSecret' => $intent->client_secret],
    //         'تم إنشاء clientSecret بنجاح',
    //         true,
    //         200
    //     );
    // }
    public function createPaymentIntent(Request $request)
{
    try {
        $validated = $request->validate([
            'amount' => 'required|integer|min:100',
            'currency' => 'sometimes|string|size:3',
        ]);

        $amount = $validated['amount'];
        $currency = $validated['currency'] ?? 'usd';

        $intent = $this->stripeService->createPaymentIntent($amount, $currency);

        return $this->apiResponse(
            ['clientSecret' => $intent->client_secret],
            'تم إنشاء clientSecret بنجاح',
            true,
            200
        );
    } catch (\Illuminate\Validation\ValidationException $e) {
        return $this->apiResponse(
            null,
            $e->getMessage(),
            false,
            422
        );
    } catch (\Stripe\Exception\ApiErrorException $e) {
        return $this->apiResponse(
            null,
            'حدث خطأ من Stripe: ' . $e->getMessage(),
            false,
            500
        );
    } catch (\Exception $e) {
        return $this->apiResponse(
            null,
            'حدث خطأ غير متوقع: ' . $e->getMessage(),
            false,
            500
        );
    }
}

}
