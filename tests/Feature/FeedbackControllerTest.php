<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.github.token' => 'fake-token',
        'services.github.repo' => 'acme/repo',
        'services.anthropic.key' => 'fake-anthropic-key',
    ]);
});

function fakeAnthropicClassification(string $type, string $title)
{
    return Http::response([
        'content' => [['type' => 'text', 'text' => json_encode(['type' => $type, 'title' => $title])]],
    ], 200);
}

test('feedback submission creates a github issue with metadata', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response(null, 500),
        'api.github.com/*' => Http::response(['number' => 1], 201),
    ]);
    $user = User::factory()->create(['name' => 'Loic', 'email' => 'loic@example.com']);

    $response = $this->actingAs($user)->post('/feedback', [
        'message' => 'Le total ne se met pas à jour',
        'page_url' => '/dashboard',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    Http::assertSent(function ($request) use ($user) {
        return $request->url() === 'https://api.github.com/repos/acme/repo/issues'
            && $request->hasHeader('Authorization', 'Bearer fake-token')
            && str_contains($request['body'], 'Le total ne se met pas à jour')
            && str_contains($request['body'], $user->email)
            && str_contains($request['body'], '/dashboard');
    });
});

test('feedback submission uses the ai-classified label and title', function () {
    Http::fake([
        'api.anthropic.com/*' => fakeAnthropicClassification('bug', 'Le total ne se met pas à jour'),
        'api.github.com/*' => Http::response(['number' => 1], 201),
    ]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/feedback', [
        'message' => 'le total ne se met pas à jour après ajout dépense',
        'page_url' => '/dashboard',
    ]);

    $response->assertSessionHasNoErrors();

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://api.github.com/repos/acme/repo/issues') {
            return true;
        }

        return $request['title'] === 'Le total ne se met pas à jour'
            && $request['labels'] === ['bug'];
    });
});

test('feedback submission falls back gracefully when classification fails', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response(null, 500),
        'api.github.com/*' => Http::response(['number' => 1], 201),
    ]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/feedback', [
        'message' => 'Bug sur le dashboard',
        'page_url' => '/dashboard',
    ]);

    $response->assertSessionHasNoErrors();

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://api.github.com/repos/acme/repo/issues') {
            return true;
        }

        return $request['labels'] === [] && str_contains($request['title'], 'Bug sur le dashboard');
    });
});

test('feedback submission falls back gracefully when classification response is malformed', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => 'not json']]], 200),
        'api.github.com/*' => Http::response(['number' => 1], 201),
    ]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/feedback', [
        'message' => 'Bug sur le dashboard',
        'page_url' => '/dashboard',
    ]);

    $response->assertSessionHasNoErrors();

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://api.github.com/repos/acme/repo/issues') {
            return true;
        }

        return $request['labels'] === [];
    });
});

test('feedback submission requires a non-empty message', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/feedback', [
        'message' => '',
        'page_url' => '/dashboard',
    ]);

    $response->assertSessionHasErrors('message');
});

test('feedback submission rejects a whitespace-only message', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/feedback', [
        'message' => "   \n  ",
        'page_url' => '/dashboard',
    ]);

    $response->assertSessionHasErrors('message');
});

test('feedback submission surfaces an error when github is unreachable', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response(null, 500),
        'api.github.com/*' => Http::response(null, 500),
    ]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/feedback', [
        'message' => 'Bug sur le dashboard',
        'page_url' => '/dashboard',
    ]);

    $response->assertSessionHasErrors('message');
});

test('a guest cannot submit feedback', function () {
    $response = $this->post('/feedback', [
        'message' => 'Bug sur le dashboard',
        'page_url' => '/dashboard',
    ]);

    $response->assertRedirect('/login');
});

test('feedback submission is throttled after 5 requests per minute', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response(null, 500),
        'api.github.com/*' => Http::response(['number' => 1], 201),
    ]);
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->actingAs($user)->post('/feedback', [
            'message' => "Bug {$i}",
            'page_url' => '/dashboard',
        ])->assertSessionHasNoErrors();
    }

    $response = $this->actingAs($user)->post('/feedback', [
        'message' => 'Bug 6',
        'page_url' => '/dashboard',
    ]);

    $response->assertStatus(429);
});
