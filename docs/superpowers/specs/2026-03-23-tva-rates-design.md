# TVA Rates Nomenclator & Product VAT Support

**Date:** 2026-03-23
**Status:** Approved

## Overview

Add a global TVA rates nomenclator (managed by Super Admin) and attach a configurable VAT class to products. When a fiscal receipt is sent to HopoFiscalBridge, each product item carries its `vatClass` index instead of the current hardcoded value of `1`. Both the web app (hopo-platform) and the local bridge service (HopoFiscalBridge) require changes.

---

## Context

HopoFiscalBridge generates Datecs-format `.txt` files consumed by ECR Bridge, which drives the physical cash register. The item line format is:

```
I;product_name;quantity;price;vat_class
```

`vat_class` is currently hardcoded to `1` in `ecrBridge.service.ts`. Different products may carry different Romanian VAT rates (19%, 9%, 5%), each mapped to a class index (1, 2, 3...) configured on the cash register.

**Scope:**
- Only **products** (`products` table) get a configurable VAT class.
- **Packages** and the **"Ora de joacă"** play-time item always use `vatClass: 1` (unchanged).
- Existing products without a TVA rate set fall back to `vatClass: 1` (no regression).

---

## 1. Database Schema

### New table: `tva_rates`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | e.g. "Cotă standard", "Cotă redusă alimentară" |
| `percentage` | decimal(5,2) | e.g. 19.00, 9.00, 5.00 |
| `vat_class` | tinyint unsigned | cash register class index (1–9); **unique constraint** — two rates cannot share the same class index |
| `is_active` | boolean | default true |
| `created_at` / `updated_at` | timestamps | |

### Modified table: `products`

- Add column `tva_rate_id` (bigint FK → `tva_rates.id`, nullable, `onDelete('set null')`)

### No changes to: `packages`, `play_sessions`, `standalone_receipt_items`

---

## 2. Web App Changes (hopo-platform)

### 2a. Models

**New model: `TvaRate`**
- `$fillable`: `name`, `percentage`, `vat_class`, `is_active`
- `$casts`: `percentage` → `'decimal:2'` (returns string, consistent with `Product::price`), `is_active` → `'boolean'`
- Relation: `hasMany(Product::class)`

**Updated model: `Product`**
- Add `tva_rate_id` to `$fillable`
- Add relation method `tvaRate()`: `return $this->belongsTo(TvaRate::class, 'tva_rate_id');` (nullable — returns null if not set)

### 2b. TVA Rates CRUD (Super Admin only)

**Controller:** `TvaRateController`
**Routes** (all require Super Admin, added to `routes/web.php`):
```
GET    /tva-rates                → index
GET    /tva-rates/create         → create form
POST   /tva-rates                → store
GET    /tva-rates/{tvaRate}/edit → edit form
PUT    /tva-rates/{tvaRate}      → update
DELETE /tva-rates/{tvaRate}      → destroy
```

**Delete guard:** if `$tvaRate->products()->exists()`, redirect back with error flash:
`"Nu se poate șterge o cotă TVA care este folosită de produse."` — consistent with redirect-with-flash pattern used elsewhere in the app.

**Views:** `resources/views/tva-rates/` — `index.blade.php` (table: name, percentage%, vat_class, active badge, edit/delete buttons), `create.blade.php`, `edit.blade.php`.

### 2c. Product Controller + Form — TVA Dropdown

**`ProductController::create()`** — pass TVA rates to view:
```php
$tvaRates = TvaRate::where('is_active', true)->orderBy('percentage')->get();
return view('products.create', compact('location', 'tvaRates'));
```

**`ProductController::edit()`** — same addition:
```php
$tvaRates = TvaRate::where('is_active', true)->orderBy('percentage')->get();
return view('products.edit', compact('product', 'tvaRates'));
```

**`ProductController::store()` and `update()`** — add validation rule:
```php
'tva_rate_id' => 'nullable|exists:tva_rates,id',
```
(Accepts any existing rate, including inactive ones set before a rate was deactivated — no restriction to `is_active=true` on update, to avoid breaking products already assigned a deactivated rate.)

**Views** (`create.blade.php`, `edit.blade.php`) — add "Cotă TVA" dropdown:
- Blank option: `"Fără cotă specifică (default clasa 1)"` → value `""`
- Options: each `TvaRate` as `"$name ($percentage%)"` → value `$id`
- Pre-select `$product->tva_rate_id` on edit

### 2d. prepareFiscalPrint — Add vatClass per item

Three code paths in two controllers must be updated. In all paths, `vatClass` defaults to `1` when a product has no rate set.

---

**`SessionsController::prepareFiscalPrint()` — single session**

Products are loaded via `$session->products` (relation `PlaySessionProduct` → `Product`). Load the TVA rate eagerly:
```php
$session = $sessionQuery->with(['products.product.tvaRate', 'location'])->first();
```

When building the items array (both the normal path and the amount-voucher discount path), add `vatClass`:
```php
// Normal path (else branch, ~line 392):
$items[] = [
    'name'     => $product['name'],
    'quantity' => $product['quantity'],
    'price'    => (float) $product['unit_price'],
    'vatClass' => $product['vat_class'] ?? 1,
];

// Amount-voucher discount path (~line 376):
$items[] = [
    'name'     => $line['name'],
    'quantity' => $line['quantity'],
    'price'    => (float) $line['discounted_unit_price'],
    'vatClass' => $line['vat_class'] ?? 1,
];
```

The intermediate `$products` collection (built from `$session->products->map(...)`) must include `vat_class`:
```php
return [
    'name'      => $productName,
    'quantity'  => $sp->quantity,
    'unit_price'=> (float) $sp->unit_price,
    'total_price'=> (float) $sp->total_price,
    'vat_class' => $sp->product?->tvaRate?->vat_class ?? 1,
];
```

In `allocateAmountDiscountAcrossLines`, the input lines must carry `vat_class` through, and the output lines will preserve it because the method mutates the input array by only adding `discounted_total_price` and `discounted_unit_price` keys — it does not rebuild lines from scratch, so extra keys survive. **This passthrough is implicit;** when modifying `allocateAmountDiscountAcrossLines` in future, ensure `vat_class` (and any other domain-specific keys) are explicitly preserved in the returned lines. Verify `$discountableLines` includes `vat_class` when built:
```php
$discountableLines[] = [
    'type'       => 'product',
    'name'       => $product['name'],
    'quantity'   => $product['quantity'],
    'unit_price' => (float) $product['unit_price'],
    'total_price'=> (float) $product['total_price'],
    'vat_class'  => $product['vat_class'] ?? 1,
];
```

"Ora de joacă" time items always get `'vatClass' => 1` hardcoded.

---

**`SessionsController::prepareCombinedFiscalPrint()` — combined sessions**

The combined path follows the same structure as single session but loops over multiple sessions. Apply the same changes:
- Eager-load `products.product.tvaRate` on each session query
- Add `vat_class` to the intermediate product map
- Add `vatClass` to every `$items[]` push (both normal and voucher discount branches)
- "Ora de joacă" time items always `vatClass: 1`

---

**`StandaloneReceiptController::prepareFiscalPrint()` — standalone receipts**

`StandaloneReceiptItem` has `source_type` ('product' or 'package') and `source_id`. To avoid N+1 queries, pre-load all source products in a single query before mapping:

```php
$standaloneReceipt->load('items');

// Pre-load all product VAT rates in one query (avoid N+1)
$productIds = $standaloneReceipt->items
    ->where('source_type', 'product')
    ->pluck('source_id');
$productsById = Product::with('tvaRate')
    ->whereIn('id', $productIds)
    ->get()
    ->keyBy('id');

$items = $standaloneReceipt->items->map(function (StandaloneReceiptItem $item) use ($productsById) {
    $vatClass = 1; // default for packages and unknown types
    if ($item->source_type === 'product') {
        $vatClass = $productsById->get($item->source_id)?->tvaRate?->vat_class ?? 1;
    }
    return [
        'name'     => $item->name,
        'quantity' => $item->quantity,
        'price'    => (float) $item->unit_price,
        'vatClass' => $vatClass,
    ];
})->values()->all();
```

**Note — standalone voucher discount:** `StandaloneReceiptController` applies amount-voucher discounts as a total reduction on `$finalPrice`, without per-line allocation. This is intentional and differs from `SessionsController` which uses `allocateAmountDiscountAcrossLines`. The `$items` array always reflects raw unit prices; the voucher discount appears at receipt level only. Do not apply per-line allocation here.

No new relation needed on `StandaloneReceiptItem`.

---

### 2e. Navigation

Add "Cote TVA" link in the Super Admin sidebar/navigation menu, alongside other global settings entries.

---

## 3. HopoFiscalBridge Changes (TypeScript)

### 3a. `src/utils/validator.ts`

Add optional `vatClass` field to `receiptItemSchema`:
```typescript
vatClass: z.number().int().min(1).max(9).optional(),
```

### 3b. `src/services/ecrBridge.service.ts`

In `generateFileContent`, replace the hardcoded VAT class in the live-mode item line:
```typescript
// Before:
return `I;${item.name};${item.quantity};${formattedPrice};1`;

// After:
const vatClass = item.vatClass ?? 1;
return `I;${item.name};${item.quantity};${formattedPrice};${vatClass}`;
```

Legacy requests without `vatClass` on items default to `1` via `?? 1` — no regression.

---

## 4. File Inventory

### hopo-platform — New files
```
database/migrations/YYYY_MM_DD_create_tva_rates_table.php
database/migrations/YYYY_MM_DD_add_tva_rate_id_to_products_table.php
app/Models/TvaRate.php
app/Http/Controllers/TvaRateController.php
resources/views/tva-rates/index.blade.php
resources/views/tva-rates/create.blade.php
resources/views/tva-rates/edit.blade.php
```

### hopo-platform — Modified files
```
app/Models/Product.php                               — add tva_rate_id to $fillable + tvaRate() relation
app/Http/Controllers/ProductController.php           — pass $tvaRates to create/edit; accept tva_rate_id in store/update
resources/views/products/create.blade.php            — add TVA dropdown
resources/views/products/edit.blade.php              — add TVA dropdown
app/Http/Controllers/SessionsController.php          — add vatClass to product items (single + combined + voucher paths)
app/Http/Controllers/StandaloneReceiptController.php — add vatClass to items via inline source_type check
routes/web.php                                       — add tva-rates resource routes
resources/views/layouts/app.blade.php                — add "Cote TVA" menu link
```

### HopoFiscalBridge — Modified files
```
src/utils/validator.ts            — add optional vatClass to receiptItemSchema
src/services/ecrBridge.service.ts — use item.vatClass ?? 1 in live-mode item line
```

---

## 5. Backwards Compatibility

- Existing products with `tva_rate_id = null` → `vatClass: 1` in bridge payload (same as today)
- HopoFiscalBridge requests without `vatClass` on items → defaults to `1` via `?? 1`
- No data migration needed — Super Admin sets rates manually on existing products via database or UI
