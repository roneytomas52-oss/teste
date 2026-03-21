CREATE TABLE store_team_members (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  store_id UUID NOT NULL REFERENCES stores(id) ON DELETE CASCADE,
  full_name VARCHAR(160) NOT NULL,
  email VARCHAR(160) NOT NULL,
  phone VARCHAR(30),
  role_slug VARCHAR(40) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'invited',
  permissions JSONB NOT NULL DEFAULT '[]'::jsonb,
  last_login_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT store_team_members_role_check CHECK (role_slug IN ('manager', 'catalog', 'operations', 'finance', 'support')),
  CONSTRAINT store_team_members_status_check CHECK (status IN ('invited', 'active', 'suspended')),
  CONSTRAINT store_team_members_email_unique UNIQUE (store_id, email)
);
