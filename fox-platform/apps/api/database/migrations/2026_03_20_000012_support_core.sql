CREATE TABLE support_tickets (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  scope VARCHAR(20) NOT NULL,
  partner_account_id UUID REFERENCES partner_accounts(id) ON DELETE CASCADE,
  store_id UUID REFERENCES stores(id) ON DELETE CASCADE,
  driver_profile_id UUID REFERENCES driver_profiles(id) ON DELETE CASCADE,
  created_by_user_id UUID REFERENCES users(id) ON DELETE SET NULL,
  channel VARCHAR(40) NOT NULL,
  assigned_team VARCHAR(40),
  priority VARCHAR(20) NOT NULL DEFAULT 'normal',
  status VARCHAR(20) NOT NULL DEFAULT 'open',
  subject VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  last_message_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT support_tickets_scope_check CHECK (scope IN ('partner', 'driver')),
  CONSTRAINT support_tickets_priority_check CHECK (priority IN ('normal', 'high', 'critical')),
  CONSTRAINT support_tickets_status_check CHECK (status IN ('open', 'in_progress', 'answered', 'resolved')),
  CONSTRAINT support_tickets_owner_check CHECK (
    (scope = 'partner' AND partner_account_id IS NOT NULL AND store_id IS NOT NULL AND driver_profile_id IS NULL)
    OR
    (scope = 'driver' AND driver_profile_id IS NOT NULL AND partner_account_id IS NULL AND store_id IS NULL)
  )
);

CREATE TABLE support_messages (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  ticket_id UUID NOT NULL REFERENCES support_tickets(id) ON DELETE CASCADE,
  sender_user_id UUID REFERENCES users(id) ON DELETE SET NULL,
  sender_role VARCHAR(40) NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
