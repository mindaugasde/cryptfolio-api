<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use RuntimeException;

class BinanceController extends Controller
{
    /**
     * Get price in USDT from Binance API.
     *
     * @param string $currency
     * @return float
     */
    public static function getExchangeRate(string $currency): float
    {
        $currency .= 'USDT';
        $url = 'https://api.binance.com/api/v3/ticker/price?symbol=' . $currency;

        $rate = self::getRatesFromApi($url);
        $rate = json_decode($rate, false);

        return (float)$rate->price;
    }

    /**
     * Get prices in USDT from Binance API.
     *
     * @return array
     */
    public static function getExchangeRates(): array
    {
        $prices = [
            'BTCUSDT' => (float)0,
            'ETHUSDT' => (float)0,
            'IOTAUSDT' => (float)0,
        ];
        $url = 'https://api.binance.com/api/v3/ticker/price';

        $rates = self::getRatesFromApi($url);
        $rates = json_decode($rates, false);

        foreach ($rates as $rate) {
            if (array_key_exists($rate->symbol, $prices)) {
                $prices[$rate->symbol] = $rate->price;
            }
        }

        return $prices;
    }

    /**
     * Get exchange rate from external API in two different formats.
     *
     * @param string $url
     * @return string
     */
    private static function getRatesFromApi(string $url): string
    {
        $content = '';
        try {
            $stream = curl_init($url);
            if ($stream === false) {
                throw new RuntimeException('Failed to initialize');
            }

            curl_setopt_array($stream, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => 1,
            ]);

            $content = curl_exec($stream);
            if ($content === false) {
                throw new RuntimeException(curl_error($stream), curl_errno($stream));
            }

            curl_close($stream);

            # Check response content for external errors
            if (self::isJson($content)) {
                $decodedContent = json_decode($content, false);
                if (isset($decodedContent->code)) {
                    throw new RuntimeException($decodedContent->msg, $decodedContent->code);
                }
            } else {
                throw new RuntimeException('Response from Binance API is not in JSON format');
            }

        } catch (RuntimeException $e) {
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }

        return $content;
    }

    /**
     * JSON variable check.
     *
     * @param string $string
     * @return bool
     */
    private static function isJson(string $string): bool
    {
        json_decode($string, false);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}
