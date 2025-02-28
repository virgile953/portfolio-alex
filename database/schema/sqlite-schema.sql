CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "products"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "sku" varchar,
  "unit_cost" numeric not null,
  "stock" integer not null default '0',
  "category" varchar,
  "image_path" varchar,
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "products_sku_unique" on "products"("sku");
CREATE TABLE IF NOT EXISTS "customers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar,
  "phone" varchar,
  "address" text,
  "city" varchar,
  "state" varchar,
  "zip" varchar,
  "country" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "invoices"(
  "id" integer primary key autoincrement not null,
  "invoice_number" varchar not null,
  "customer_id" integer not null,
  "issue_date" date not null,
  "due_date" date not null,
  "total_amount" numeric not null default '0',
  "status" varchar not null default 'draft',
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("customer_id") references "customers"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "invoice_product"(
  "id" integer primary key autoincrement not null,
  "invoice_id" integer not null,
  "product_id" integer not null,
  "quantity" integer not null default '1',
  "unit_cost" numeric not null,
  "total_cost" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("invoice_id") references "invoices"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");

INSERT INTO migrations VALUES(1,'2023_06_10_000000_create_products_table',1);
INSERT INTO migrations VALUES(2,'2024_02_28_000001_create_customers_table',1);
INSERT INTO migrations VALUES(3,'2024_02_28_000003_create_invoices_table',1);
INSERT INTO migrations VALUES(4,'2024_02_28_000004_create_invoice_product_table',1);
INSERT INTO migrations VALUES(5,'2025_02_28_000000_add_columns_to_products_table',1);
INSERT INTO migrations VALUES(6,'2025_02_28_003127_create_sessions_table',1);
INSERT INTO migrations VALUES(7,'2025_02_28_003545_add_missing_columns_to_invoices_table',1);
INSERT INTO migrations VALUES(8,'2025_02_28_005056_create_users_table',1);
