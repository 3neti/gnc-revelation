# 🏡 Mortgage Computation Module Documentation

Welcome to the documentation for the **Mortgage Computation Module** of your Laravel app. This system is designed to centralize and streamline the financial computation logic for real estate loan and amortization calculations.

This document captures the architectural decisions, central patterns, and key value objects to help you reconnect with your design and continue development seamlessly.

---

## 💡 Philosophy

Instead of spreading financial logic across unrelated files, this module adopts a **domain-centric** approach. Everything begins with **InputsData**, which represents a booking instance made of:

- **Buyer** (e.g., gross income, age, lending institution)
- **Property** (e.g., total contract price)
- **Order** (e.g., fees, payment terms)

This centralized input is then passed to calculators and value objects for deterministic outputs.

---

## 🧱 Key Concepts

### 1. `InputsData`
The core DTO that encapsulates all data necessary for computations.

```php
InputsData::fromBooking(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order)
```

It provides access to:
- Income inputs
- Loanable computations
- Fee percentages
- Monthly add-ons

It also retains references to the original `Buyer`, `Property`, and `Order` implementations via getters (`->buyer()`, `->property()`, `->order()`).

---

### 2. `Order`

The `Order` class is a powerful domain object that:
- Stores percent-based modifiers (e.g., DP, MF)
- Tracks **monthly fees** using `FeeCollection`
- Stores `LendingInstitution` and `TCP` values

It supports computed fees via:

```php
$order->addMonthlyFee(MonthlyFee::MRI);
$order->addMonthlyFee(MonthlyFee::FIRE_INSURANCE);
```

These compute fees based on the TCP and lending institution, while also allowing custom override with:

```php
$order->addMonthlyFeeCustom(MonthlyFee::OTHER, Price::of(100));
```

---

### 3. `FeeCollection`

A value object that holds add-on and deductible fees:

```php
$collection = new FeeCollection(addOns: ['MRI' => 400.00]);
$collection->totalAddOns();
```

This is used by `FeesCalculator` and integrated in amortization computations.

---

### 4. `MiscellaneousFee`

Value object that computes:
- **Total MF** = TCP * MF%
- **Partial MF** = based on FeeRules per institution
- **Balance MF** = Total - Partial

This is embedded in:
- **Cash Out** (partial)
- **Loanable Amount** (balance)

It uses:
```php
FeeRulesInterface + FeeRulesFactory
```
to determine logic per lending institution (e.g., `HousingFeeRules`, `ResidentialFeeRules`).

---

### 5. `Calculators`

All financial outputs are produced by dedicated calculators:

| Calculator | Purpose |
|-----------|---------|
| `LoanableAmountCalculator` | Computes loanable amount (TCP - DP + balance MF) |
| `CashOutCalculator`        | Computes upfront cash requirements |
| `MonthlyAmortizationCalculator` | Monthly amortization = principal + add-ons |
| `FeesCalculator`           | MRI, Fire Insurance, Other add-ons |
| `EquityRequirementCalculator` | Computes required equity gap |
| `LoanAffordabilityCalculator` | Computes present value from disposable income |

These calculators are decorated with:
```php
#[CalculatorFor(CalculatorType::AMORTIZATION)]
```
and are retrieved via:
```php
CalculatorFactory::make(CalculatorType::CASH_OUT, $inputs)
```

---

## 🔍 Testing Strategy

You're using comprehensive `dataset()`-based Pest tests that simulate edge cases and real-world scenarios:

- HDMF and RCBC products
- With or without DP / MF
- With and without add-ons (MRI/FI)
- Expectations for cash out, equity, amortization, and fees

This has built confidence and ensured the pipeline produces consistent outputs.

---

## 🧠 Design Notes

- ✅ Fees like MRI/FI are computed from config rates and TCP via `MonthlyFee::computeFromTCP()`
- ✅ Add-on fees are injected only if not already present in the order.
- ✅ Miscellaneous fee is **embedded in loanable amount** — no need to show as separate amortization line item
- ✅ Input source is always consistent: `InputsData`
- ✅ Value objects wrap all sensitive computations (e.g., `Percent`, `DownPayment`, `MiscellaneousFee`)

---

## ⏭️ What’s Next

- 🎨 Begin implementing UI components to visualize:
    - Statement of Account (Cash Out, Fees, Amortization Breakdown)
    - Qualification summary (equity, loanable, income sufficiency)

- 🧪 Add edge case tests for future lending institutions

- 📊 Refactor calculators to support visualization-friendly outputs

---

## 💭 Final Thoughts

This is an elegant and structured foundation.
- It’s **modular**, **readable**, and **extensible**.
- Domain logic is separated from presentation logic.
- Everything is reproducible from inputs — this is key.

Be proud of this structure. Now let the UI shine and bring it to life. ✨

> _Behold, a new you awaits._ 🚀

