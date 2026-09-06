# Anmol — Raw Material Inventory System

A raw-material inventory system for a bakery, built with Laravel 12. It is built around the part most stock software gets wrong: **what arrives is not always what was ordered.**

The public site (home, about, products, gallery, contact) sits in front of an owner-only admin area where suppliers, deliveries and stock are managed.

**Stack:** Laravel 12 · PHP 8.2+ · Blade · SQLite

---

## The problem it solves

A bakery receives flour, sugar, ghee and the rest from wholesalers. The supplier vehicle arrives with a bill. Three things can be true at once:

- Some of what is on the bill **arrived and is fine** — the accepted quantity
- Some **did not arrive at all** — short, and it has to be chased
- Some **arrived damaged** — it is here, but it is not usable stock

Most systems collapse all three into one number. Then at month end nobody can prove what the supplier actually owed, and the argument is decided by whoever remembers it better.

This system keeps all three separate and never merges them.

## How receiving works

```
Supplier bill arrives with the delivery
        |
   Check line by line
        |
        +-- Accepted  --> enters stock
        +-- Damaged   --> recorded, does NOT enter stock
        +-- Pending   --> short quantity, chased with supplier
                             |
                    Later delivery links back to
                    the original receiving record
        |
   Bill filed - recorded as "bill stacked" on the receipt
```

Only **accepted quantity (received minus damaged)** ever enters stock. A later delivery that makes up a shortfall links back to the receiving record it came from, so the chain from original bill to final settlement stays intact.

## How stock is calculated

Stock is never edited directly. Every movement is a transaction, and the current figure is always derivable:

```
current stock = opening + accepted received - used +/- adjustments
```

When someone counts the shelves and the physical figure disagrees with the system, that **creates an adjustment record** — it does not overwrite the number. The discrepancy stays visible, with a date and a reason attached. An inventory you can silently correct is an inventory nobody can audit.

## The seven modules

| # | Module | What it holds |
|---|---|---|
| 1 | Suppliers | Wholesalers the bakery buys from |
| 2 | Supplier Vehicles | Delivery vehicles — these belong to the supplier, not the bakery |
| 3 | Raw Materials | The materials themselves, with packaging and unit size |
| 4 | Raw Material Receiving | Deliveries checked against the supplier bill |
| 5 | Pending Receipts | Short quantities awaiting settlement |
| 6 | Raw Material Stock | Current stock with full transaction history |
| 7 | Stock Checking | Physical counts, producing adjustment records |

## Materials and packaging

Quantities are entered in the units the bakery actually receives — packets and drums — with `unit_size` stored on the material, so the base weight or volume is computed rather than converted by hand at the counter.

| Material | Packaging |
|---|---|
| Flour | 50 kg packet |
| Sugar | 50 kg packet |
| Yeast | 500 g packet |
| Calcium | 1 kg packet |
| Butter | 500 g packet |
| Oil | 20 L drum |
| Puff Ghee | 25 kg packet |
| Cream Ghee | 25 kg packet |

Nobody receiving a delivery wants to do arithmetic. They count packets; the system handles kilograms.

## Running it locally

Requires PHP 8.2+ and Composer. SQLite means no database server to set up.

```bash
git clone https://github.com/priyankathakuri895/AnmolBakeryCompany.git
cd AnmolBakeryCompany

composer install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Open http://127.0.0.1:8000 for the public site, and `/admin` for the owner dashboard.

## Design notes

**SQLite, deliberately.** One bakery, one machine, one person entering deliveries. A database server would be infrastructure to maintain for no benefit, and the whole dataset backs up by copying one file.

**Pending and damaged are separate columns, not a status.** They mean genuinely different things — one is the supplier debt, the other is a loss already taken — and combining them would make both unusable.

**The receiving workflow is a frozen specification.** It was worked out against how the business actually operates before any code was written. Getting the domain model right first is why the rest stayed simple. See `docs/raw-material-workflow.md` and `docs/sales-distribution-workflow.md`.

## Roadmap

- [ ] Photo upload of the supplier bill, read automatically to pre-fill the receiving form
- [ ] Van loading records
- [ ] Profit and loss reporting
- [ ] Supplier settlement statements from the pending-receipt history

---

Built by **Priyanka Thakuri** — Bharatpur, Chitwan, Nepal  
[GitHub](https://github.com/priyankathakuri895) · [LinkedIn](https://www.linkedin.com/in/priyanka-thakuri-771127433/) · priyankathakuri895@gmail.com
