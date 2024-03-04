<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Service\CommissionCalculationInterface;
use App\Model\DTO\TransactionDTO;
use DateTime;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'app:calculate-commission',
    description: 'Add a short description for your command',
)]
class CalculateCommissionCommand extends Command
{
    public function __construct(
        private readonly CommissionCalculationInterface $commissionFeeCalculationService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Calculate commission fees for each transaction from a CSV file.')
            ->setHelp('This command calculates commission fees based on defined rules.')
            ->addArgument('csv-file-path', InputArgument::REQUIRED, 'Path to CSV file with data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csvFilePath = $input->getArgument('csv-file-path');

        try {
            // Validate if the file exists
            if (!is_file($csvFilePath)) {
                throw new InvalidArgumentException(sprintf('File %s not found', $csvFilePath));
            }

            $csvFile = fopen($csvFilePath, 'rb');
            while (($transactionData = fgetcsv($csvFile)) !== false) {
                /** @var array $transactionData */
                $transactionDTO = new TransactionDTO(
                    date: new DateTime($transactionData[0]),
                    userId: (int) $transactionData[1],
                    userType: $transactionData[2],
                    transactionType: $transactionData[3],
                    amount: (float) $transactionData[4],
                    currency: $transactionData[5],
                );
                $calculatedFee = $this->commissionFeeCalculationService->getCommissionFee($transactionDTO);
                $io->write($calculatedFee.PHP_EOL);
            }
            fclose($csvFile);
        } catch (Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
