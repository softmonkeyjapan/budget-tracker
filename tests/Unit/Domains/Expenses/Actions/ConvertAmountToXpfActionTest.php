<?php

use App\Domains\Expenses\Actions\ConvertAmountToXpfAction;
use App\Domains\Expenses\Contracts\ExchangeRateProviderInterface;

it('passes XPF amounts through, rounded', function () {
    $rates = Mockery::mock(ExchangeRateProviderInterface::class);
    $rates->shouldReceive('usdToEurRate')->never();

    $action = new ConvertAmountToXpfAction($rates);

    expect($action->execute(500.4, 'XPF'))->toBe(500);
});

it('converts EUR using the fixed legal peg', function () {
    $rates = Mockery::mock(ExchangeRateProviderInterface::class);
    $rates->shouldReceive('usdToEurRate')->never();

    $action = new ConvertAmountToXpfAction($rates);

    expect($action->execute(10, 'EUR'))->toBe((int) round(10 * 119.331742));
});

it('converts USD using the live rate when available', function () {
    $rates = Mockery::mock(ExchangeRateProviderInterface::class);
    $rates->shouldReceive('usdToEurRate')->once()->andReturn(0.5);

    $action = new ConvertAmountToXpfAction($rates);

    expect($action->execute(100, 'usd'))->toBe((int) round(100 * 0.5 * 119.331742));
});

it('falls back to the configured rate when the live USD rate is unavailable', function () {
    config(['services.exchange_rate.usd_xpf_fallback' => 100]);

    $rates = Mockery::mock(ExchangeRateProviderInterface::class);
    $rates->shouldReceive('usdToEurRate')->once()->andReturn(null);

    $action = new ConvertAmountToXpfAction($rates);

    expect($action->execute(10, 'USD'))->toBe(1000);
});

it('throws for an unsupported currency', function () {
    $rates = Mockery::mock(ExchangeRateProviderInterface::class);

    $action = new ConvertAmountToXpfAction($rates);

    expect(fn () => $action->execute(10, 'GBP'))->toThrow(InvalidArgumentException::class);
});
