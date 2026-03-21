CREATE TABLE notifications (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  scope VARCHAR(20) NOT NULL,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  level VARCHAR(20) NOT NULL DEFAULT 'info',
  context VARCHAR(40) NOT NULL DEFAULT 'operations',
  title VARCHAR(180) NOT NULL,
  body TEXT NOT NULL,
  action_label VARCHAR(120),
  action_url VARCHAR(255),
  is_read BOOLEAN NOT NULL DEFAULT FALSE,
  read_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT notifications_scope_check CHECK (scope IN ('partner', 'driver', 'admin')),
  CONSTRAINT notifications_level_check CHECK (level IN ('info', 'success', 'warning', 'danger'))
);

CREATE INDEX idx_notifications_scope_user_created_at
  ON notifications (scope, user_id, created_at DESC);
