<?php

use App\Models\Category;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('categories index page is displayed', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create(['name' => 'Alimentaire']);
    Category::factory()->child($root)->create(['name' => 'Boucherie']);

    $response = $this->actingAs($user)->get('/categories');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Categories/Index')
        ->has('categories', 1)
        ->where('categories.0.name', 'Alimentaire')
        ->has('categories.0.children', 1)
        ->where('categories.0.children.0.name', 'Boucherie')
    );
});

test('a root category can be created', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/categories', [
        'name' => 'Alimentaire',
        'color' => '#FF8A66',
        'icon' => '🍔',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/categories');

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'parent_id' => null,
        'name' => 'Alimentaire',
        'color' => '#FF8A66',
    ]);
});

test('a child category can be created under a root category', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();

    $response = $this->actingAs($user)->post('/categories', [
        'name' => 'Boucherie',
        'parent_id' => $root->id,
    ]);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'parent_id' => $root->id,
        'name' => 'Boucherie',
    ]);
});

test('a category cannot be created under a category that is itself a child', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();

    $response = $this->actingAs($user)->post('/categories', [
        'name' => 'Trop profond',
        'parent_id' => $child->id,
    ]);

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseMissing('categories', ['name' => 'Trop profond']);
});

test('a category cannot be created under another user\'s category', function () {
    $user = User::factory()->create();
    $otherUsersRoot = Category::factory()->create();

    $response = $this->actingAs($user)->post('/categories', [
        'name' => 'Alimentaire',
        'parent_id' => $otherUsersRoot->id,
    ]);

    $response->assertSessionHasErrors('parent_id');
});

test('a category can be updated by its owner', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['name' => 'Old name']);

    $response = $this->actingAs($user)->put("/categories/{$category->id}", [
        'name' => 'New name',
        'color' => '#2F80ED',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/categories');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'New name',
        'color' => '#2F80ED',
    ]);
});

test('a category cannot be updated by another user', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)->put("/categories/{$category->id}", [
        'name' => 'Hijacked',
    ]);

    $response->assertForbidden();
});

test('a category without children can be deleted', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete("/categories/{$category->id}");

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/categories');
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('a category with children cannot be deleted', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    Category::factory()->child($root)->create();

    $response = $this->actingAs($user)->delete("/categories/{$root->id}");

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseHas('categories', ['id' => $root->id]);
});

test('a category cannot be deleted by another user', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)->delete("/categories/{$category->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});
