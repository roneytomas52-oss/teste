CREATE TABLE store_bank_accounts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  store_id UUID NOT NULL REFERENCES stores(id) ON DELETE CASCADE,
  bank_name VARCHAR(120) NOT NULL,
  bank_code VARCHAR(20),
  branch_number VARCHAR(30) NOT NULL,
  account_number VARCHAR(40) NOT NULL,
  account_type VARCHAR(30) NOT NULL DEFAULT 'checking',
  account_holder_name VARCHAR(180) NOT NULL,
  account_holder_document VARCHAR(40) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (store_id),
  CONSTRAINT store_bank_accounts_type_check CHECK (account_type IN ('checking', 'savings')),
  CONSTRAINT store_bank_accounts_status_check CHECK (status IN ('pending', 'validated', 'rejected'))
);

CREATE TABLE payout_requests (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  store_id UUID NOT NULL REFERENCES stores(id) ON DELETE CASCADE,
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  scheduled_for TIMESTAMPTZ NOT NULL,
  amount NUMERIC(12,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'scheduled',
  note TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT payout_requests_status_check CHECK (status IN ('scheduled', 'processing', 'completed', 'blocked', 'pending_review'))
);

CREATE TABLE wallet_transactions (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  store_id UUID NOT NULL REFERENCES stores(id) ON DELETE CASCADE,
  order_id UUID REFERENCES orders(id) ON DELETE SET NULL,
  payout_request_id UUID REFERENCES payout_requests(id) ON DELETE SET NULL,
  direction VARCHAR(10) NOT NULL,
  transaction_type VARCHAR(30) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'processed',
  description VARCHAR(255) NOT NULL,
  amount NUMERIC(12,2) NOT NULL DEFAULT 0,
  occurred_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT wallet_transactions_direction_check CHECK (direction IN ('credit', 'debit')),
  CONSTRAINT wallet_transactions_type_check CHECK (transaction_type IN ('order_revenue', 'platform_fee', 'payout', 'adjustment', 'refund')),
  CONSTRAINT wallet_transactions_status_check CHECK (status IN ('processed', 'scheduled', 'sent', 'under_review'))
);
