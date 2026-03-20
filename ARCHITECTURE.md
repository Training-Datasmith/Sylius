# Sylius Architecture

## Purpose

Sylius is a customisable e-commerce framework for Symfony. Rather than a monolithic CMS, it
provides decoupled Components and Bundles that can be used independently or as a complete
storefront solution with API Platform 4.x support.

## Directory Structure

```
src/Sylius/
  Component/          # Pure PHP domain logic — no Symfony dependency
    Core/             # Cross-cutting domain: Order, Product, Channel, Customer
    Order/            # Order aggregate (items, adjustments, state machine)
    Product/          # Product, variant, option, taxon domain
    Payment/          # Payment model and method resolution contracts
    Promotion/        # Promotion rules, actions, coupon logic
    Shipping/         # Shipment, method and cost calculator contracts
    Inventory/        # Stock availability checking
    Currency/          # Currency conversion utilities
    Taxation/         # Tax rate and calculator contracts
  Bundle/             # Symfony bundles wrapping Components
    CoreBundle/       # Doctrine mappings, services.xml, event subscribers
    AdminBundle/      # Twig admin UI templates and controllers
    ShopBundle/       # Twig storefront templates and controllers
    ApiBundle/        # API Platform 4.x resources and serialization
```

## Key Design Decisions

- **Component / Bundle separation**: Components hold domain models and interfaces with zero
  framework dependency. Bundles wire components to Symfony via DI, Doctrine, and routing.
- **Interface-driven design**: Every entity has a matching `*Interface`. Sylius only type-hints
  on interfaces, allowing application-level overrides via `sylius_*` resource configuration.
- **Resource layer (SyliusResource)**: Entity CRUD is handled by the `sylius.resource` system
  built on top of API Platform. Controllers, routing, and serialization are generated from
  XML resource definitions.
- **State Machine**: Order lifecycle (cart → checkout → fulfillment) is managed by
  Winzou StateMachine via `OrderTransitions` constants. Callbacks are Symfony event subscribers.
- **Adjustments**: Financial mutations (taxes, promotions, shipping) are modelled as
  `Adjustment` objects attached to orders and items rather than ad-hoc fields.
- **Channel architecture**: Multi-store behaviour is achieved through `Channel` objects.
  Pricing, currencies, locales, and tax zones are all channel-scoped.

## Extension Points

- Override any entity by creating a class that extends the Sylius model and configuring
  `sylius_*: resources: order: classes: model: App\Entity\Order`.
- Add custom checkout steps by implementing a new state machine callback or event subscriber.
- Add custom promotion rules via `sylius.promotion_rule_checker` tagged service.
- Add custom shipping calculators via `sylius.shipping_calculator` tagged service.
- Extend the API by adding new API Platform resource definitions under `ApiBundle/Resources/`.

## Dependency Flow

```
StorefrontController / ApiController
  └─> OrderProcessor (runs OrderProcessorInterface chain)
        └─> OrderItemsSubtotalCalculator
        └─> AdjustmentsClearer
        └─> TaxationProcessor
              └─> TaxRateResolver
        └─> ShippingProcessor
              └─> ShippingMethodsResolver
        └─> PromotionProcessor
              └─> PromotionApplicator
```

## PHP Version Requirements

PHP 8.2+. Declare `strict_types=1` in all files. Use `final readonly` for services.
