<?php
declare(strict_types=1);

use App\Http\Controllers\BinanceController;
use Laravel\Lumen\Testing\DatabaseMigrations;

class BinanceControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function binance_api_returns_exchange_for_one_currency(): void
    {
        $bitcoinExchange = BinanceController::getExchangeRate('BTC');
        $this->assertGreaterThan(0, $bitcoinExchange);
        $this->assertIsFloat($bitcoinExchange);
    }

    /** @test */
    public function binance_api_returns_all_declared_currencies(): void
    {
        $allExchanges = BinanceController::getExchangeRates();
        $this->assertArrayHasKey('BTCUSDT', $allExchanges);
        $this->assertArrayHasKey('ETHUSDT', $allExchanges);
        $this->assertArrayHasKey('IOTAUSDT', $allExchanges);
        $this->assertIsArray($allExchanges);
    }
}
