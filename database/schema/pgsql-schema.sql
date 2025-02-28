CREATE TABLE IF NOT EXISTS "migrations" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "migration" VARCHAR(255) NOT NULL,
  "batch" INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS "products" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "name" VARCHAR(255) NOT NULL,
  "description" TEXT,
  "sku" VARCHAR(255),
  "unit_cost" NUMERIC NOT NULL,
  "stock" INTEGER NOT NULL DEFAULT '0',
  "category" VARCHAR(255),
  "image_path" VARCHAR(255),
  "notes" TEXT,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);
CREATE UNIQUE INDEX "products_sku_unique" ON "products"("sku") WHERE "sku" IS NOT NULL;

CREATE TABLE IF NOT EXISTS "customers" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "name" VARCHAR(255) NOT NULL,
  "email" VARCHAR(255),
  "phone" VARCHAR(255),
  "address" TEXT,
  "city" VARCHAR(255),
  "state" VARCHAR(255),
  "zip" VARCHAR(255),
  "country" VARCHAR(255),
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "invoices" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "invoice_number" VARCHAR(255) NOT NULL,
  "customer_id" INTEGER NOT NULL,
  "issue_date" DATE NOT NULL,
  "due_date" DATE NOT NULL,
  "total_amount" NUMERIC NOT NULL DEFAULT '0',
  "status" VARCHAR(255) NOT NULL DEFAULT 'draft',
  "notes" TEXT,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP,
  FOREIGN KEY("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "invoice_product" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "invoice_id" INTEGER NOT NULL,
  "product_id" INTEGER NOT NULL,
  "quantity" INTEGER NOT NULL DEFAULT '1',
  "unit_cost" NUMERIC NOT NULL,
  "total_cost" NUMERIC NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP,
  FOREIGN KEY("invoice_id") REFERENCES "invoices"("id") ON DELETE CASCADE,
  FOREIGN KEY("product_id") REFERENCES "products"("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "sessions" (
  "id" VARCHAR(255) PRIMARY KEY NOT NULL,
  "user_id" INTEGER,
  "ip_address" VARCHAR(45),
  "user_agent" TEXT,
  "payload" TEXT NOT NULL,
  "last_activity" INTEGER NOT NULL
);
CREATE INDEX "sessions_user_id_index" ON "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" ON "sessions"("last_activity");

CREATE TABLE IF NOT EXISTS "users" (
  "id" SERIAL PRIMARY KEY NOT NULL,
  "name" VARCHAR(255) NOT NULL,
  "email" VARCHAR(255) NOT NULL,
  "email_verified_at" TIMESTAMP,
  "password" VARCHAR(255),
  "remember_token" VARCHAR(100),
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);
CREATE UNIQUE INDEX "users_email_unique" ON "users"("email");
