# Anmol — Raw Material Workflow Specification

Status: agreed baseline. Business logic is frozen; the database schema below implements it.
**Receiving (module 4) and Pending Receipts (module 5) are now built** — `MaterialReceiptController`
(`app/Http/Controllers/Admin/MaterialReceiptController.php`), covering sections 1–9 below,
plus a bill photo upload (`material_receipts.bill_image_path`, stored on the `public` disk).
One simplification: follow-up deliveries always use `link_type = pending_fulfilment`; a
dedicated `damage_replacement` flow was not built (the enum case exists but is unused) since
it's a rarer case and the pending-fulfilment flow already covers "the rest of what's owed."
Raw Material Stock (module 6) and Stock Checking (module 7) — sections 10–11 — remain unbuilt.

---

## 1. Supply chain

Raw material is delivered by wholesalers/suppliers. **The vehicles belong to the suppliers** — the
business does not own or dispatch delivery vans. A supplier may supply several materials, and a
material may come from several suppliers.

```
Supplier -> Supplier's vehicle -> Warehouse
```

## 2. Delivery arrival

Each arriving delivery is recorded with: supplier, vehicle, driver name (optional), date,
bill/invoice number and bill date.

## 3. Checking against the bill

For every raw material line on the bill:

| Field        | Meaning                                     |
| ------------ | ------------------------------------------- |
| Bill qty     | What the supplier's bill says               |
| Received qty | What physically arrived                     |
| Damaged qty  | Arrived but unusable                        |
| Accepted qty | received - damaged; the only qty that stocks |
| Pending qty  | bill - received; still owed by the supplier |

Pending and Damaged are tracked **separately** and never merged.

## 4. Packaging units

Staff count in packets/drums. The system stores the standard size of one unit so base quantity
(kg / L / g) is derived automatically.

| Raw material | Unit   | Unit size |
| ------------ | ------ | --------- |
| Flour        | Packet | 50 kg     |
| Sugar        | Packet | 50 kg     |
| Yeast        | Packet | 500 g     |
| Calcium      | Packet | 1 kg      |
| Butter       | Packet | 500 g     |
| Oil          | Drum   | 20 L      |
| Puff Ghee    | Packet | 25 kg     |
| Cream Ghee   | Packet | 25 kg     |

35 flour packets => 35 x 50 = 1,750 kg. Quantities are entered and reported in packets/drums;
base quantity is computed, not typed.

## 5. Pending material

Bill 35, received 32 => pending 3. The supplier is contacted for the remaining 3. The line stays
**Pending**; the 3 packets are neither lost nor damaged.

## 6. Pending arrives later

A follow-up delivery is recorded as a new receipt linked to the original one
(`parent_receipt_id`), and each line links to the original line (`parent_item_id`,
`link_type = pending_fulfilment`). The original line's pending qty is reduced.

```
Bill 35 -> first delivery 32 -> pending 3 -> later delivery 3 -> total 35 -> Complete
```

A partial follow-up (2 of 3) leaves the line Pending with 1 outstanding.

## 7. Damaged material

Damaged qty is recorded on its own and never becomes stock. If the supplier agrees to replace it,
the replacement is recorded as another linked line
(`link_type = damage_replacement`).

## 8. Only accepted material stocks

```
Bill 35, received 32, damaged 1, pending 3  =>  accepted 31  =>  stock +31 packets
```

## 9. Bill stacking

After checking, the physical bill is filed. The receipt carries `bill_stacked` and
`bill_stacked_at`.

## 10. Raw material stock

```
Current stock = Opening + Accepted received - Used +/- Adjustments
```

`raw_materials.current_stock` holds the running balance; `stock_transactions` holds the ledger
(one signed row per movement, with `balance_after`) so the balance can always be explained.

## 11. Physical stock check

Staff count the warehouse. System qty vs physical qty gives a difference, with a reason
(counting error, damage, missing, other). Finalising a check writes an **adjustment**
transaction — the stock number is never silently overwritten.

## 12. Supplier vehicles

Vehicles are kept per supplier, so every receipt records which supplier and which vehicle brought
the delivery.

## 13. Overall flow

```
SUPPLIER -> SUPPLIER VEHICLE -> RAW MATERIAL ARRIVES -> CHECK AGAINST BILL
                                   |            |            |
                               ACCEPTED      PENDING      DAMAGED
                                   |            |            |
                                   |     CONTACT SUPPLIER    |
                                   |            |            |
                                   |     REMAINING ARRIVES   |
                                   +------------+------------+
                                                |
                                          BILL STACKED
                                                |
                                            MAIN STOCK
                                                |
                                        STOCK TRANSACTIONS
                                                |
                                       PHYSICAL STOCK CHECK
                                                |
                                            ADJUSTMENT
```

## 14. Modules

1. Suppliers / Wholesalers
2. Supplier Vehicles
3. Raw Materials
4. Raw Material Receiving
5. Pending Receipts
6. Raw Material Stock
7. Stock Checking

---

## 15. Schema map

| Table                    | Purpose                                                   |
| ------------------------ | --------------------------------------------------------- |
| `suppliers`              | Wholesalers                                                |
| `supplier_vehicles`      | Vehicles belonging to a supplier                           |
| `raw_materials`          | Materials + unit definition + running stock                |
| `material_receipts`      | One delivery (header): supplier, vehicle, bill, status     |
| `material_receipt_items` | One material line per delivery; bill/received/damaged/pending/accepted, self-linked for follow-ups |
| `stock_transactions`     | Signed stock ledger with `balance_after`, polymorphic ref  |
| `stock_checks`           | A physical count session                                   |
| `stock_check_items`      | System vs physical per material, with reason               |

Enums live in `app/Enums`: `ReceiptStatus`, `ReceiptType`, `ReceiptItemStatus`, `ReceiptLinkType`,
`StockTransactionType`, `StockCheckStatus`, `StockDifferenceReason`, `BaseUnit`.
