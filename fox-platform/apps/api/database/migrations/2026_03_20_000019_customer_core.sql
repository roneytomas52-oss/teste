CREATE TABLE customer_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
  city VARCHAR(120),
  state VARCHAR(120),
  marketing_opt_in BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

ALTER TABLE orders
  ADD COLUMN customer_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  ADD COLUMN customer_email VARCHAR(160) NULL;

CREATE INDEX idx_orders_customer_user_id ON orders (customer_user_id);
