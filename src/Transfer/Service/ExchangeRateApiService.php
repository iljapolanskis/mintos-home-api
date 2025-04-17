<?php
declare(strict_types=1);

namespace App\Transfer\Service;

use App\Transfer\Api\ExchangeRateServiceInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Transfer\Entity\Enum\CurrencyEnum;

/**
 * @see https://exchangeratesapi.io/documentation/
 */
class ExchangeRateApiService implements ExchangeRateServiceInterface
{
    public const BASE_URL = 'https://api.exchangeratesapi.io/v1/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly RatesCache $cache,
        private readonly string $keyExchangeRateApiService,
    ) {
    }

    /**
     * Convert an amount from one currency to another
     *
     * @param float $amount Amount to convert
     * @param CurrencyEnum $fromCurrency Source currency
     * @param CurrencyEnum $toCurrency Target currency
     * @return float Converted amount
     * @throws \InvalidArgumentException If the conversion fails
     */
    public function convert(float $amount, CurrencyEnum $fromCurrency, CurrencyEnum $toCurrency): float
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }

        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);

        return $amount * $rate;
    }

    /**
     * Get the exchange rate between two currencies
     *
     * @param CurrencyEnum $fromCurrency Source currency
     * @param CurrencyEnum $toCurrency Target currency
     * @return float The exchange rate
     * @throws \InvalidArgumentException If the exchange rate couldn't be retrieved
     */
    public function getExchangeRate(CurrencyEnum $fromCurrency, CurrencyEnum $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $from = $fromCurrency->name;
        $to = $toCurrency->name;

        $baseRates = $this->cache->getRates();
        if (!$baseRates) {
            $baseRates = $this->fetchRatesForUSD();
            $this->cache->saveRatesForCurrency(CurrencyEnum::USD, $baseRates);
        }

        // Example Rate(EUR→CNY) = Rate(USD→CNY) / Rate(USD→EUR)
        $fromRate = $baseRates[$from] ?? null;
        $toRate = $baseRates[$to] ?? null;
        if ($fromRate === null || $toRate === null) {
            throw new \InvalidArgumentException('Invalid currency conversion');
        }

        $rate = $toRate / $fromRate;

        return $rate;
    }

    private function fetchRatesForUSD(): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . 'latest', [
            'query' => [
                'access_key' => $this->keyExchangeRateApiService,
            ],
        ]);

        $ratesJson = $response->getContent(false);
        $rates = json_decode($ratesJson, true)['rates'] ?? [];

        if (empty($rates)) {
            throw new BadRequestException('No rates found in the response');
        }

        return $rates;
    }
}
