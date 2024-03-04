<?php

namespace App\Tests\Unit;

use App\Contract\Service\CurrencyExchangeInterface;
use App\Contract\Storage\TransactionStorageInterface;
use App\Formatter\AmountFormatter;
use App\Model\DTO\TransactionDTO;
use App\Service\CommissionCalculationService;
use App\Service\CommissionStrategyService;
use App\Service\CurrencyExchangeService;
use App\Storage\ArrayTransactionStorage;
use App\Strategy\BusinessWithdrawalCommissionStrategy;
use App\Strategy\DepositCommissionStrategy;
use App\Strategy\PrivateWithdrawalCommissionStrategy;
use DateTime;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal
 */
final class CommissionCalculationServiceTest extends TestCase
{
    private ClientInterface|MockObject $httpClientMock;
    private TransactionStorageInterface $transactionStorage;
    private CurrencyExchangeInterface|MockObject $currencyExchangeService;
    private CommissionCalculationService $commissionCalculationService;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        // Create mock objects for dependencies
        $this->httpClientMock = $this->createMock(ClientInterface::class);
        $this->transactionStorage = new ArrayTransactionStorage();
        $numberFormatter = new AmountFormatter(['JPY', 'KRW']);
        // Commission fee strategies service and register strategies
        $commissionStrategyService = new CommissionStrategyService();
        $commissionStrategyService->addStrategy(new DepositCommissionStrategy(
            0.0003,  // env.DEPOSIT_DEFAULT_COMMISSION_FEE
        ));
        $commissionStrategyService->addStrategy(new BusinessWithdrawalCommissionStrategy(
            0.005,  // env.DEPOSIT_DEFAULT_COMMISSION_FEE
        ));
        $commissionStrategyService->addStrategy(new PrivateWithdrawalCommissionStrategy(
            0.003,  // env.WITHDRAW_PRIVATE_COMMISSION_FEE
            1000,   // env.WITHDRAW_PRIVATE_WEEKLY_FREE_AMOUNT
            3,      // env.WITHDRAW_PRIVATE_WEEKLY_FREE_TRANSACTION_COUNT
        ));
        // Mocked currency change service
        $this->currencyExchangeService = new CurrencyExchangeService(
            $this->httpClientMock,
            'serviceUrl',
            'apiKey',
            'EUR', // Assuming ERU is the base currency
        );

        // Instantiate CommissionCalculationService with dependencies
        $this->commissionCalculationService = new CommissionCalculationService(
            $this->transactionStorage,
            $commissionStrategyService,
            $this->currencyExchangeService,
            $numberFormatter,
        );
    }

    public function testGetCommissionFee(): void
    {
        // Mock the response from the API
        $response = [
            'success' => true,
            'rates' => [
                'EUR' => 1.0,
                'USD' => 1.1497,
                'JPY' => 129.53,
            ],
        ];
        $this->httpClientMock->expects($this->any())
            ->method('request')
            ->with('GET', 'serviceUrl', [
                RequestOptions::QUERY => [
                    'access_key' => 'apiKey',
                    'base' => $this->currencyExchangeService->getBaseCurrency(),
                ],
            ])
            ->willReturn($this->createMockResponse($response));

        // Process each transaction and check the commission fee
        foreach ($this->getInputData() as $data) {
            $transactionData = $data['transaction'];
            $expectedCommissionFee = $data['expectedCommissionFee'];

            // Create TransactionInterface object
            $transaction = new TransactionDTO(
                date: new DateTime($transactionData['date']),
                userId: $transactionData['userId'],
                userType: $transactionData['userType'],
                transactionType: $transactionData['transactionType'],
                amount: (float) $transactionData['amount'],
                currency: $transactionData['currency'],
            );
            // Call the method under test
            $result = $this->commissionCalculationService->getCommissionFee($transaction);

            // Assertions
            $this->assertEquals($expectedCommissionFee, $result);
        }
    }

    private function createMockResponse(array $exchangeRates): ResponseInterface|MockObject
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->expects($this->any())
            ->method('getContents')
            ->willReturn(json_encode($exchangeRates, JSON_THROW_ON_ERROR));

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->any())
            ->method('getBody')
            ->willReturn($streamMock);

        return $responseMock;
    }

    private function getInputData(): array
    {
        return [
            [
                'transaction' => [
                    'date' => '2014-12-31',
                    'userId' => 4,
                    'userType' => 'private',
                    'transactionType' => 'withdraw',
                    'amount' => 1200.00,
                    'currency' => 'EUR',
                ],
                'expectedCommissionFee' => '0.60',
            ],
            [
                'transaction' => [
                    'date' => '2015-01-01',
                    'userId' => 4,
                    'userType' => 'private',
                    'transactionType' => 'withdraw',
                    'amount' => 1000.00,
                    'currency' => 'EUR',
                ],
                'expectedCommissionFee' => '3.00',
            ],
            [
                'transaction' => [
                    'date' => '2016-01-05',
                    'userId' => 4,
                    'userType' => 'private',
                    'transactionType' => 'withdraw',
                    'amount' => 1000.00,
                    'currency' => 'EUR',
                ],
                'expectedCommissionFee' => '0.00',
            ],
            [
                'transaction' => [
                    'date' => '2016-01-05',
                    'userId' => 1,
                    'userType' => 'private',
                    'transactionType' => 'deposit',
                    'amount' => 200.00,
                    'currency' => 'EUR',
                ],
                'expectedCommissionFee' => '0.06',
            ],
            [
                'transaction' => [
                    'date' => '2016-01-06',
                    'userId' => 2,
                    'userType' => 'business',
                    'transactionType' => 'withdraw',
                    'amount' => 300.00,
                    'currency' => 'EUR',
                ],
                'expectedCommissionFee' => '1.50',
            ],
            [
                'transaction' => [
                    'date' => '2016-01-06',
                    'userId' => 1,
                    'userType' => 'private',
                    'transactionType' => 'withdraw',
                    'amount' => 30000,
                    'currency' => 'JPY',
                ],
                'expectedCommissionFee' => '0',
            ],
            [
                'transaction' => [
                    'date' => '2016-01-07',
                    'userId' => 1,
                    'userType' => 'private',
                    'transactionType' => 'withdraw',
                    'amount' => 1000.00,
                    'currency' => 'EUR',
                ],
                'expectedCommissionFee' => '0.70',
            ],
            [
                'transaction' => [
                    'date' => '2016-01-07',
                    'userId' => 1,
                    'userType' => 'private',
                    'transactionType' => 'withdraw',
                    'amount' => 100.00,
                    'currency' => 'USD',
                ],
                'expectedCommissionFee' => '0.30',
            ],
            [
                'transaction' => [
                    'date' => '2016-01-10',
                    'userId' => 1,
                    'userType' => 'private',
                    'transactionType' => 'withdraw',
                    'amount' => 100.00,
                    'currency' => 'EUR',
                ],
                'expectedCommissionFee' => '0.30',
            ],
            [
                'transaction' => [
                    'date' => '2016-01-10',
                    'userId' => 2,
                    'userType' => 'business',
                    'transactionType' => 'deposit',
                    'amount' => 10000.00,
                    'currency' => 'EUR',
                ],
                'expectedCommissionFee' => '3.00',
            ],
            [
                'transaction' => [
                    'date' => '2016-01-10',
                    'userId' => 3,
                    'userType' => 'private',
                    'transactionType' => 'withdraw',
                    'amount' => 1000.00,
                    'currency' => 'EUR',
                ],
                'expectedCommissionFee' => '0.00',
            ],
            [
                'transaction' => [
                    'date' => '2016-02-15',
                    'userId' => 1,
                    'userType' => 'private',
                    'transactionType' => 'withdraw',
                    'amount' => 300.00,
                    'currency' => 'EUR',
                ],
                'expectedCommissionFee' => '0.00',
            ],
            [
                'transaction' => [
                    'date' => '2016-02-19',
                    'userId' => 5,
                    'userType' => 'private',
                    'transactionType' => 'withdraw',
                    'amount' => 3000000,
                    'currency' => 'JPY',
                ],
                'expectedCommissionFee' => '8612',
            ],
        ];
    }
}
