ALTER TABLE driver_profiles
  ADD COLUMN bank_name VARCHAR(120),
  ADD COLUMN bank_branch_number VARCHAR(30),
  ADD COLUMN bank_account_number VARCHAR(40),
  ADD COLUMN rating NUMERIC(3,2) NOT NULL DEFAULT 0,
  ADD COLUMN last_active_at TIMESTAMPTZ;

CREATE TABLE driver_documents (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  driver_profile_id UUID NOT NULL REFERENCES driver_profiles(id) ON DELETE CASCADE,
  document_type VARCHAR(40) NOT NULL,
  label VARCHAR(120) NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  storage_path TEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT driver_documents_status_check CHECK (status IN ('pending', 'approved', 'rejected'))
);

CREATE TABLE driver_availability_slots (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  driver_profile_id UUID NOT NULL REFERENCES driver_profiles(id) ON DELETE CASCADE,
  weekday SMALLINT NOT NULL,
  starts_at TIME NOT NULL,
  ends_at TIME NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'open',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT driver_availability_slots_weekday_check CHECK (weekday BETWEEN 0 AND 6),
  CONSTRAINT driver_availability_slots_status_check CHECK (status IN ('open', 'partial', 'closed'))
);

CREATE TABLE driver_wallet_transactions (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  driver_profile_id UUID NOT NULL REFERENCES driver_profiles(id) ON DELETE CASCADE,
  order_id UUID REFERENCES orders(id) ON DELETE SET NULL,
  direction VARCHAR(10) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'processed',
  amount NUMERIC(12,2) NOT NULL DEFAULT 0,
  description VARCHAR(255) NOT NULL,
  occurred_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT driver_wallet_transactions_direction_check CHECK (direction IN ('credit', 'debit')),
  CONSTRAINT driver_wallet_transactions_status_check CHECK (status IN ('processed', 'scheduled', 'under_review'))
);
