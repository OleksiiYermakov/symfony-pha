<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\Service\CurrencyExchangeInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

class CurrencyExchangeService implements CurrencyExchangeInterface
{
    /** @var array<string, float> */
    private array $exchangeRatesCache = [];

    public function __construct(
        private readonly ClientInterface $guzzleHttpClient,
        private readonly string $serviceUrl,
        private readonly string $apiKey,
        private readonly string $baseCurrency,
    ) {
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount; // No conversion needed for same currencies
        }

        if ($fromCurrency === $this->baseCurrency) {
            return $amount * $this->getExchangeRate($toCurrency);
        }

        if ($toCurrency === $this->baseCurrency) {
            return $amount / $this->getExchangeRate($fromCurrency);
        }

        // Convert via base currency
        $amountInBaseCurrency = $amount / $this->getExchangeRate($fromCurrency);

        return $amountInBaseCurrency * $this->getExchangeRate($toCurrency);
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    private function getExchangeRate(string $currency): float
    {
        // Try to get exchange rate from the cache
        if (isset($this->exchangeRatesCache[$currency])) {
            return $this->exchangeRatesCache[$currency];
        }

        // If not found in the cache, we send request to API, store in a cache rates and get required exchange rate
        $exchangeRates = $this->getExchangeRatesFromApi();
        if (isset($exchangeRates[$currency])) {
            return $exchangeRates[$currency];
        }

        throw new InvalidArgumentException(sprintf('Exchange rate for currency [%s] not found.', $currency));
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    private function getExchangeRatesFromApi(): array
    {
        $response = $this->sendRequest();

        if (!$response['success']) {
            throw new RuntimeException($response['error']['info']);
        }

        return $this->exchangeRatesCache = $response['rates'];
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    private function sendRequest(): array
    {
        $json = $this->guzzleHttpClient
            ->request(
                'GET',
                $this->serviceUrl,
                [
                    RequestOptions::QUERY => ['access_key' => $this->apiKey, 'base' => $this->getBaseCurrency()],
                ],
            )
            ->getBody()
            ->getContents();

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}
