CREATE TABLE partner_accounts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  owner_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  legal_name VARCHAR(200) NOT NULL,
  document_number VARCHAR(30) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT partner_accounts_status_check CHECK (status IN ('draft', 'pending', 'active', 'suspended', 'rejected'))
);

CREATE TABLE stores (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  partner_account_id UUID NOT NULL REFERENCES partner_accounts(id) ON DELETE CASCADE,
  trade_name VARCHAR(160) NOT NULL,
  legal_name VARCHAR(200) NOT NULL,
  document_number VARCHAR(30) NOT NULL,
  email VARCHAR(160),
  phone VARCHAR(30),
  status VARCHAR(20) NOT NULL DEFAULT 'draft',
  city VARCHAR(120),
  state VARCHAR(120),
  country VARCHAR(120) NOT NULL DEFAULT 'Brasil',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT stores_status_check CHECK (status IN ('draft', 'pending', 'active', 'paused', 'suspended', 'rejected'))
);

CREATE TABLE driver_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  modal VARCHAR(20) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  city VARCHAR(120),
  state VARCHAR(120),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT driver_profiles_modal_check CHECK (modal IN ('bike', 'moto', 'carro')),
  CONSTRAINT driver_profiles_status_check CHECK (status IN ('pending', 'active', 'suspended', 'rejected'))
);

CREATE TABLE admin_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  department VARCHAR(80),
  is_super BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (user_id)
);

