<?php

use App\Domains\Expenses\Actions\ParseBciNotificationAction;

it('parses a recognized BCI card payment notification', function () {
    $body = "BCI\nPaiement carte\nPaiement de 17,99 USD sur la CB 5136...5202 chez NETFLIX.COM.\nEn cas de fraude, faites opposition sur BCInet ou au +33 4 42 60 77 70.";

    $result = (new ParseBciNotificationAction)->execute($body);

    expect($result)->not->toBeNull();
    expect($result->amount)->toBe(17.99);
    expect($result->currency)->toBe('USD');
    expect($result->merchant)->toBe('NETFLIX.COM');
});

it('parses amounts in EUR and XPF', function () {
    $eur = (new ParseBciNotificationAction)->execute('Paiement de 42,50 EUR sur la CB 1234...5678 chez CARREFOUR.PF.');
    $xpf = (new ParseBciNotificationAction)->execute('Paiement de 1500 XPF sur la CB 1234...5678 chez MAGASIN.');

    expect($eur->currency)->toBe('EUR');
    expect($eur->amount)->toBe(42.50);
    expect($xpf->currency)->toBe('XPF');
    expect($xpf->amount)->toBe(1500.0);
});

it('returns null for an unrecognized notification format', function () {
    $result = (new ParseBciNotificationAction)->execute('Votre solde du compte est de 500 000 XPF.');

    expect($result)->toBeNull();
});

it('returns null for an unsupported currency', function () {
    $result = (new ParseBciNotificationAction)->execute('Paiement de 10,00 GBP sur la CB 1234...5678 chez SHOP.');

    expect($result)->toBeNull();
});
