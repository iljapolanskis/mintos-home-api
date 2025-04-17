<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/.env.local');

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();

try {
    $exchangeRateApiService = $container->get(App\Transfer\Service\ExchangeRateApiService::class);

    $amountFrom = 100;
    $fromCurrency = App\Transfer\Entity\Enum\CurrencyEnum::CNY;
    $toCurrency = App\Transfer\Entity\Enum\CurrencyEnum::EUR;

    $value = $exchangeRateApiService->convert(
        $amountFrom,
        $fromCurrency,
        $toCurrency
    );

    echo "Converted $amountFrom{$fromCurrency->name} to $value{$toCurrency->name}" . PHP_EOL;

    $debug = 1;
} catch (\Throwable $throwable) {
    echo "Error: " . $throwable->getMessage() . PHP_EOL;
    echo $throwable->getTraceAsString() . PHP_EOL;
} finally {
    $kernel->shutdown();
}
