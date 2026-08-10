# Règles — Tests

## Principe

L'architecture par Actions/DTOs/Repositories existe en partie pour rendre le code trivialement testable sans HTTP ni base de données réelle quand ce n'est pas nécessaire. En profiter.

## Organisation des tests

Miroir de la structure des domaines :

```
tests/
├── Unit/
│   └── Domains/
│       └── Billing/
│           └── Actions/
│               └── PlaceOrderActionTest.php
└── Feature/
    └── Domains/
        └── Billing/
            └── Http/
                └── PlaceOrderTest.php
```

## Règles par type de fichier

### Actions → test Unit avec Repository mocké

```php
it('crée une commande et débite le paiement', function () {
    $orders = Mockery::mock(OrderRepositoryInterface::class);
    $gateway = Mockery::mock(PaymentGatewayInterface::class);

    $orders->shouldReceive('create')->once()->andReturn(new Order(['id' => 1, 'total' => 4200]));
    $gateway->shouldReceive('charge')->once()->with('pm_123', 4200);

    $action = new PlaceOrderAction($orders, $gateway);

    $order = $action->execute(new PlaceOrderData(
        customerId: 1,
        lines: [['product_id' => 1, 'quantity' => 2]],
        shippingMethodId: 1,
    ));

    expect($order->id)->toBe(1);
});
```

Pas de `RefreshDatabase` ici — l'Action est testée en isolation totale via les interfaces mockées. Rapide, pas de DB.

### Endpoints API → test Feature avec DB réelle (RefreshDatabase)

```php
it('POST /api/v1/orders crée une commande', function () {
    $customer = Customer::factory()->create();

    $response = $this->postJson('/api/v1/orders', [
        'customer_id' => $customer->id,
        'lines' => [['product_id' => 1, 'quantity' => 2]],
        'shipping_method_id' => 1,
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['id', 'status', 'total', 'created_at']);
});
```

Ces tests valident le câblage complet : Route → Middleware → Request → Controller → Action → Repository → Resource. À garder plus légers en nombre que les tests Unit (pyramide de tests).

### Repositories → test Unit ou Feature léger avec DB

Tester le Repository directement contre une vraie base de test (`RefreshDatabase`), sans passer par l'Action — vérifie que les requêtes Eloquent produisent le bon résultat.

### Events/Listeners

```php
it('émet OrderPlaced après la création', function () {
    Event::fake();

    // ... exécuter l'Action

    Event::assertDispatched(OrderPlaced::class);
});

it('CreateShipmentWhenOrderPlaced crée bien une expédition', function () {
    // tester le Listener isolément, event construit à la main, sans Event::fake()
});
```

Ne jamais tester dans le même test que l'event est émis ET que le listener d'un AUTRE domaine réagit correctement — ça recrée un couplage entre domaines dans les tests aussi. Un test par domaine.

## Checklist minimale par nouvelle fonctionnalité

- [ ] Un test Unit pour chaque nouvelle Action (cas nominal + au moins un cas d'échec métier).
- [ ] Un test Feature pour chaque nouvel endpoint API (statut HTTP + structure JSON).
- [ ] Un test pour chaque nouveau Listener inter-domaine, isolé.
- [ ] Pas de test qui dépend de l'ordre d'exécution des autres tests (chaque test doit passer seul).
