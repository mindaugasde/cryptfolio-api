<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Asset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AssetController extends Controller
{
    /**
     * Return all user's assets.
     */
    public function index(): JsonResponse
    {
        $userAssets = Asset::where('user_id', Auth::id())->get();
        if (!count($userAssets)) {
            return $this->apiResponse('You doesn\'t have any assets created', [], 200);
        }

        $exchangeRates = BinanceController::getExchangeRates();
        $exchangeTo = 'USDT';
        $assetsTotal = [];
        foreach ($userAssets as $userAsset) {
            $userAsset['current_price'] = self::getCurrentPrice($userAsset, $exchangeRates, $exchangeTo);
            $userAsset['difference_price'] = self::getDiffPrice($userAsset);
            $userAsset['difference_percent'] = self::getDiffPercent($userAsset);

            if (!array_key_exists($userAsset['currency'], $assetsTotal)) {
                $assetsTotal[$userAsset['currency']]['quantity'] = $userAsset['quantity'];
                $assetsTotal[$userAsset['currency']]['trade_price'] = $userAsset['trade_price'];
                $assetsTotal[$userAsset['currency']]['current_price'] = self::getCurrentPrice($userAsset, $exchangeRates, $exchangeTo);
                $assetsTotal[$userAsset['currency']]['difference_price'] = self::getDiffPrice($userAsset);
                $assetsTotal[$userAsset['currency']]['difference_percent'] = self::getDiffPercent($userAsset);
            } else {
                $assetsTotal[$userAsset['currency']]['quantity'] += $userAsset['quantity'];
                $assetsTotal[$userAsset['currency']]['trade_price'] += $userAsset['trade_price'];
                $assetsTotal[$userAsset['currency']]['current_price'] += self::getCurrentPrice($userAsset, $exchangeRates, $exchangeTo);
                $assetsTotal[$userAsset['currency']]['difference_price'] += self::getDiffPrice($userAsset);
                $assetsTotal[$userAsset['currency']]['difference_percent'] += self::getDiffPercent($userAsset);
            }
        }

        return $this->apiResponse(
            count($userAssets) . ' assets in total', [
                'total' => $assetsTotal,
                'assets' => [$userAssets],
            ], 200);
    }

    /**
     * Store a newly created asset in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'label' => 'required|string|max:191',
            'quantity' => 'required|numeric|min:0',
            'currency' => 'required|in:BTC,ETH,IOTA',
        ]);

        $exchangeRate = BinanceController::getExchangeRate($request->currency);
        if ($exchangeRate <= 0) {
            return $this->errorResponse('Something went wrong. Please try again or contact support.', 500);
        }

        $asset = Asset::create([
            'user_id' => Auth::id(),
            'label' => $request->label,
            'quantity' => $request->quantity,
            'currency' => $request->currency,
            'exchange_rate' => $exchangeRate,
            'trade_price' => $request->quantity * $exchangeRate,
            'trade_date' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return $this->apiResponse('Asset created', $asset->toArray(), 201);
    }

    /**
     * Update the specified asset in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $asset = Asset::where('user_id', Auth::id())->find($id);
        if (!$asset) {
            return $this->errorResponse('Asset not found. Try to first GET all assets to see available IDs', 404);
        }

        $this->validate($request, [
            'label' => 'sometimes|required|string|max:191',
            'quantity' => 'sometimes|required|numeric|min:0',
        ]);
        if ($request->quantity) {
            $trade_price = $request->quantity * $asset->exchange_rate;
            $request->request->add(['trade_price' => $trade_price]);
        }
        $asset->update($request->all());

        return $this->apiResponse('Asset updated', $asset->toArray(), 200);
    }

    /**
     * Remove the specified asset from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $asset = Asset::where('user_id', Auth::id())->find($id);
        if (!$asset) {
            return $this->errorResponse('Asset not found. Try to first GET all assets to see available IDs', 404);
        }

        $asset->delete();

        return $this->apiResponse('Asset deleted', [], 204);
    }

    /**
     * @param string $message
     * @param array $data
     * @param int $statusCode
     * @return JsonResponse
     */
    private function apiResponse(string $message, array $data, int $statusCode): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => $data], $statusCode);
    }

    /**
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    private function errorResponse(string $message, int $statusCode): JsonResponse
    {
        return response()->json(['error' => $message], $statusCode);
    }

    /**
     * @param $userAsset
     * @param array $exchangeRates
     * @param string $exchangeTo
     * @return float|int
     */
    private static function getCurrentPrice($userAsset, array $exchangeRates, string $exchangeTo)
    {
        return $userAsset['quantity'] * $exchangeRates[$userAsset['currency'] . $exchangeTo];
    }

    /**
     * @param $userAsset
     * @return float
     */
    private static function getDiffPrice($userAsset): float
    {
        return round($userAsset['current_price'] - $userAsset['trade_price'], 4);
    }

    /**
     * @param $userAsset
     * @return float
     */
    private static function getDiffPercent($userAsset): float
    {
        return round(($userAsset['current_price'] / $userAsset['trade_price']) * 100 - 100, 4);
    }
}
