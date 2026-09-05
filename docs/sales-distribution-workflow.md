# Anmol — Sales / Distribution Workflow Specification

Status: **all modules below are built** — Products, Salesmen, Vans, finished-goods Stock,
Van Loading, Settlement/Bills + the Van Debit Ledger, Expenses, and Reports. What's left from
the owner's original four-part ask is (3) testing with real Anmol data in place of the
placeholder seed data, and (4) deployment — both need input only the owner can give (the real
product/price list, real staff names, and a hosting/domain decision).

Business-rule decisions locked in with the owner:

- **Debit is tracked per van only** — one running balance per van, not per shop/customer.
- **Returns**: unsold-but-fresh product a van brings back goes back into stock; expired or
  damaged product is a write-off/loss, not restocked.
- **Production stays simple**: finished-goods stock increases via a manual stock-in entry
  (date, product, qty) — no recipe/raw-material-consumption costing.
- **Settlement is per-van-per-day totals**, not itemized per shop/customer.

---

## 1. Catalog and fleet (built)

```
Products (finished goods) -----\
                                 >-- loaded onto --> Vans (driven by) --> Salesmen
Vans ---------------------------/
```

- `products`: name, code, unit_label (how it's sold — Piece/Packet/...), price, opening/current
  stock, reorder_level, is_active. Stock is backed by `product_stock_transactions`, a signed
  ledger with `balance_after`, exactly mirroring `stock_transactions` for raw materials — one
  ledger table per stock domain, not a shared universal one. Only `Opening` (the type recorded
  atomically when a product is created) exists today; later phases add cases to
  `ProductStockTransactionType` without a migration change, since the column is a plain string.
- `salesmen`: the staff roster (name, phone, address, is_active).
- `vans`: the delivery fleet (name/number, registration_number, is_active) with a
  `default_salesman_id` — the usual driver. A specific day's actual driver is captured on that
  day's load (see below), so a substitution never has to change the van's default.

## 2. Stock phase (built)

`product_stock_checks` / `product_stock_check_items` mirror `stock_checks` / `stock_check_items`
for raw material: starting a check snapshots every active product's `current_stock` as
`system_qty` into one item per product (physical_qty defaults to the same value until staff
edit it); saving recalculates `difference = physical - system` per line without touching stock;
finalizing is the one irreversible step — it walks every item with a non-zero difference,
writes a signed `Adjustment` `product_stock_transactions` row (referencing the check item) and
bumps `products.current_stock`, then marks the check `Finalized`. A finalized check can't be
edited or deleted, matching the deletion guard used everywhere else in this app. Separately, a
plain "record production" form (date, product, qty produced) writes a `Production` transaction
directly — the entirety of "production tracking" for now, with no raw-material
consumption/costing attached. Both `Production` and `Adjustment` were added as
`ProductStockTransactionType` cases without a migration, as planned.

## 3. Loading phase (built)

```
Van + Salesman + Date -> van_loads (header)
                            |
                    van_load_items (product, qty loaded, unit price snapshot)
                            |
                  writes a VanLoad (outward) product_stock_transaction per line
```

One `van_loads` row per van per day (`unique(van_id, load_date)`, checked with `whereDate`
before insert — a plain `date` column can still carry a `00:00:00` time part depending on the
driver, so date equality checks must go through `whereDate`, not `where`). `van_load_items`
records the actual driver for the day (`salesman_id`, independent of the van's
`default_salesman_id`, so a substitution never touches the van record) and snapshots
`unit_price` from `products.price` at load time so a later price change never rewrites
history. Stock leaves the moment it's loaded — there is no "in transit" state.

Loads are **create-only**: once a load exists it has ledger history (its stock transactions),
so — consistent with the immutability rule used everywhere else in this app (a `Product` or
`RawMaterial` can't be deleted once it has stock transactions) — a `VanLoad` has no edit or
delete route at all. A mistake made while loading is corrected later, either by the Settlement
phase's returns or by a stock check adjustment; it is never undone by editing the load itself.

## 4. Settlement / Bills phase (built) + Debit ledger phase (built)

These two ended up built together — finalizing a settlement is the *only* thing that ever
writes to the debit ledger, so the ledger table had to exist before settlement finalize could
work. The split in this doc is kept for reference, but they shipped as one pass.

```
van_loads (1:1) -> van_settlements (header: status, cash, online, debit collected/given)
                        |
                van_settlement_items (product, qty loaded/returned-fresh/returned-expired/sold, line total)
```

Starting a settlement (from the "Settle this van" button on a `van_loads` show/index row)
snapshots each `van_load_item` into a `van_settlement_item` (`qty_loaded`, `unit_price`
copied) and captures `previous_debit_balance` from the van at that moment — draft, nothing
posted yet. Saving recalculates `qty_sold = qty_loaded - qty_returned_fresh -
qty_returned_expired` and `line_total` per line without touching stock or debit (same
draft-then-finalize shape as the stock check). **Finalizing is the one irreversible step**:
for every line with `qty_returned_fresh > 0` it writes a `VanReturn` (inward)
`product_stock_transaction` and bumps `products.current_stock` — the unsold-but-fresh stock is
back and sellable tomorrow. `qty_returned_expired` writes no stock transaction (the stock
already left when loaded); it's a pure loss with no separate wastage table for now, visible
only as the difference between loaded and (fresh + sold). The header's `total_sales_value`
(sum of line totals) is meant to reconcile to `cash_collected + online_collected +
(new_debit_given - debit_collected)` — the edit view shows this difference live so a
data-entry mismatch is visible before finalizing, though nothing currently blocks finalizing
an unreconciled settlement. **The settlement *is* the daily salesman bill** — there's no
separate bill table, `van-settlements.edit` doubles as the read-only bill view once finalized.
A finalized settlement can't be edited or deleted, matching every other ledger-backed record
in this app.

Finalizing also posts the van's debit change: `vans.current_debit_balance` (a running column)
and `van_debit_transactions` (van, nullable settlement reference, signed `amount`,
`balance_after`, date, notes) — one row per settlement representing the net change
(`new_debit_given - debit_collected`) for that day. A standalone "Add adjustment" form (pick a
van, increase/decrease, amount, date, note) writes the same table with no settlement reference,
for manual corrections like writing off bad debt.

## 6. Expenses phase (built)

`expenses`: date, van_id (nullable — a fuel/toll cost is van-specific, a general business cost
is not), category (`ExpenseCategory`: Fuel, Maintenance, Salary, Rent, Utilities, Wastage,
Other), amount, description, plus a nullable `van_settlement_id` for the case where it's
logged from within a settlement (not wired into the settlement UI yet, just the column).
Unlike every stock/debit ledger table in this app, expenses carries **no running balance** —
nothing else derives its state from a total that must stay explainable — so it's the one
module in the whole sales-distribution build with ordinary, unguarded edit and delete.

## 7. Reports phase (built)

No new tables — a single date-range-filtered page (`admin.reports.index`, default range: the
current month) aggregating what the other modules already recorded: total sales (from
finalized `van_settlements`, grouped by van), total expenses (grouped by category), total
debit outstanding (`SUM(vans.current_debit_balance)`, always as-of-today since it's a running
balance, not a range), and finished-goods stock valuation (`current_stock × price` per
product, also as-of-today). Sales-by-van grouping is done in PHP over an eager-loaded
collection rather than a SQL `GROUP BY` join, matching `DashboardController`'s existing style.
Kept to plain tables, no charts — nothing in this app renders charts today (no Chart.js/Alpine
installed), so that's a deliberate deferral, not an oversight.

## 8. Deployment

Separate task, once the modules above are built and tested with real Anmol data — needs a
hosting/domain decision from the owner (out of scope for this doc).
