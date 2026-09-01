<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

function postToBciWebhook(string $body, ?string $key = 'secret-key'): TestResponse
{
    $server = $key !== null ? ['HTTP_X_WEBHOOK_KEY' => $key] : [];

    return test()->call('POST', '/webhooks/bci', [], [], [], $server, $body);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    config([
        'services.bci_webhook.key' => 'secret-key',
        'services.bci_webhook.user_id' => $this->user->id,
    ]);
});

test('the webhook rejects a request without a valid api key', function () {
    $response = postToBciWebhook('anything', key: 'wrong-key');

    $response->assertUnauthorized();
    $this->assertDatabaseCount('expenses', 0);
});

test('the webhook rejects a request with no api key at all', function () {
    $response = postToBciWebhook('anything', key: null);

    $response->assertUnauthorized();
});

test('a recognized EUR notification creates a draft expense converted to XPF', function () {
    $body = 'Paiement de 42,50 EUR sur la CB 1234...5678 chez CARREFOUR.PF.';

    $response = postToBciWebhook($body);

    $response->assertNoContent();
    $this->assertDatabaseHas('expenses', [
        'user_id' => $this->user->id,
        'status' => 'brouillon',
        'category_id' => null,
        'amount' => (int) round(42.50 * 119.331742),
        'description' => 'CARREFOUR.PF',
    ]);
});

test('a recognized USD notification uses the live exchange rate', function () {
    Http::fake([
        'api.frankfurter.dev/*' => Http::response(['rates' => ['EUR' => 0.9]]),
    ]);

    $body = 'Paiement de 17,99 USD sur la CB 5136...5202 chez NETFLIX.COM.';

    postToBciWebhook($body);

    $this->assertDatabaseHas('expenses', [
        'user_id' => $this->user->id,
        'status' => 'brouillon',
        'amount' => (int) round(17.99 * 0.9 * 119.331742),
        'description' => 'NETFLIX.COM',
    ]);
});

test('a USD notification falls back to the configured rate when the exchange api fails', function () {
    Http::fake([
        'api.frankfurter.dev/*' => Http::response(null, 500),
    ]);
    config(['services.exchange_rate.usd_xpf_fallback' => 100]);

    $body = 'Paiement de 10,00 USD sur la CB 5136...5202 chez SHOP.';

    postToBciWebhook($body);

    $this->assertDatabaseHas('expenses', [
        'user_id' => $this->user->id,
        'status' => 'brouillon',
        'amount' => 1000,
    ]);
});

test('an unrecognized notification is stored as rejected with the raw payload', function () {
    $body = 'Votre carte a été utilisée à létranger.';

    postToBciWebhook($body);

    $this->assertDatabaseHas('expenses', [
        'user_id' => $this->user->id,
        'status' => 'rejetee',
        'category_id' => null,
        'amount' => null,
        'raw_payload' => $body,
    ]);
});
