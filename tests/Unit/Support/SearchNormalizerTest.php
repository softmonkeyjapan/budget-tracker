<?php

use App\Support\SearchNormalizer;

it('lower-cases plain ASCII text', function () {
    expect(SearchNormalizer::normalize('Claude'))->toBe('claude');
});

it('strips French accents regardless of case', function () {
    expect(SearchNormalizer::normalize('Supermarché'))->toBe('supermarche')
        ->and(SearchNormalizer::normalize('ÉCOLE'))->toBe('ecole')
        ->and(SearchNormalizer::normalize('Café Français'))->toBe('cafe francais');
});

it('leaves already normalized text unchanged', function () {
    expect(SearchNormalizer::normalize('supermarche'))->toBe('supermarche');
});
