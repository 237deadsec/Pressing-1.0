# Pressing Manager module for Dolibarr 21

This repository contains a complete custom Dolibarr module located at `htdocs/custom/pressing`.

## Features

- Laundry intake with ticket number auto-generation
- Two pricing modes: by kilogram and by clothing item
- Clothing catalog with washing/ironing/dry-cleaning prices
- Workflow statuses: Received, Washing, Ironing, Ready, Delivered
- A5 printable ticket PDF
- Invoice generation with Dolibarr invoice object (`Facture`)
- Basic cash management fields (payment mode + partial payment amount)
- Search by ticket/customer/date/status
- Dashboard metrics (today deposits, ready, pending, daily revenue)

## Installation (Dolibarr 21)

1. Copy `htdocs/custom/pressing` into your Dolibarr custom directory.
2. Log in as admin, then go to **Home > Setup > Modules/Applications**.
3. Enable **Pressing Manager** module.
4. Go to **Products/Services > Pressing Manager > Setup** and configure default price per kg/payment mode.
5. Use **Products/Services > Pressing Manager > New order** to create laundry tickets.

## SQL schema

The installer loads SQL from:

- `htdocs/custom/pressing/sql/llx_pressing.sql`

It creates these tables:

- `llx_pressing_order`
- `llx_pressing_items`
- `llx_pressing_prices`
- `llx_pressing_status`

## Notes

- Module relies on standard Dolibarr modules: Third parties, Products/Services, Invoices.
- Designed for PHP 8+, MariaDB, Dolibarr v21.
- TakePOS compatibility is prepared through module hooks declaration.
