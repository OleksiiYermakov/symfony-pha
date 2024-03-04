<?php

namespace App\Tests\Unit;

use App\Contract\Service\CurrencyExchangeInterface;
use App\Service\CurrencyExchangeService;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal
 */
final class CurrencyExchangeServiceTest extends TestCase
{
    private ClientInterface|MockObject $httpClientMock;
    private CurrencyExchangeInterface $service;

    protected function setUp(): void
    {
        // Create mock objects for dependencies
        $this->httpClientMock = $this->createMock(ClientInterface::class);
        // Create an instance of CurrencyExchangeService with mock dependencies
        $this->service = new CurrencyExchangeService(
            $this->httpClientMock,
            'serviceUrl',
            'apiKey',
            'EUR', // Assuming ERU is the base currency
        );
    }

    public function testConvertSameCurrency(): void
    {
        $amount = 100.0;
        $result = $this->service->convert($amount, 'EUR', 'EUR');
        $this->assertEquals($amount, $result);
    }

    /**
     * @dataProvider exchangeRatesAndAmountProvider
     */
    public function testConvertDifferentCurrenciesToBaseCurrency(array $exchangeRates, float $amount): void
    {
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', 'serviceUrl', [
                RequestOptions::QUERY => ['access_key' => 'apiKey', 'base' => $this->service->getBaseCurrency()],
            ])
            ->willReturn($this->createMockResponse(['success' => true, 'rates' => $exchangeRates]));

        foreach ($exchangeRates as $currency => $rate) {
            $result = $this->service->convert($amount, $currency, $this->service->getBaseCurrency());
            $expectedResult = $amount / $rate;
            $this->assertEquals($expectedResult, $result);
        }
    }

    /**
     * @dataProvider exchangeRatesAndAmountProvider
     */
    public function testConvertDifferentCurrenciesFromBaseCurrency(array $exchangeRates, float $amount): void
    {
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', 'serviceUrl', [
                RequestOptions::QUERY => ['access_key' => 'apiKey', 'base' => $this->service->getBaseCurrency()],
            ])
            ->willReturn($this->createMockResponse(['success' => true, 'rates' => $exchangeRates]));

        foreach ($exchangeRates as $currency => $rate) {
            $result = $this->service->convert($amount, $this->service->getBaseCurrency(), $currency);
            $expectedResult = $amount * $rate;
            $this->assertEquals($expectedResult, $result);
        }
    }

    public function testConvertInvalidCurrency(): void
    {
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($this->createMockResponse(['success' => true, 'rates' => ['EUR' => 1]]));

        $this->expectException(InvalidArgumentException::class);
        $this->service->convert(100, 'XYZ', 'USD');
    }

    public static function exchangeRatesAndAmountProvider(): array
    {
        return [
            [
                'exchangeRates' => ['USD' => 1.1497, 'JPY' => 129.53],
                'amount' => 1500.0,
            ],
            [
                'exchangeRates' => ['USD' => 1.0497, 'JPY' => 125.35],
                'amount' => 500.0,
            ],
            [
                'exchangeRates' => ['USD' => 1.2497, 'JPY' => 100.15],
                'amount' => 100.0,
            ],
        ];
    }

    private function createMockResponse(array $exchangeRates): ResponseInterface|MockObject
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode($exchangeRates, JSON_THROW_ON_ERROR));

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);

        return $responseMock;
    }
}
