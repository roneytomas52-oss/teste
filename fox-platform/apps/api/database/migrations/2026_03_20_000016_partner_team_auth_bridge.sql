ALTER TABLE store_team_members
  ADD COLUMN IF NOT EXISTS user_id UUID REFERENCES users(id) ON DELETE SET NULL,
  ADD COLUMN IF NOT EXISTS invited_by_user_id UUID REFERENCES users(id) ON DELETE SET NULL,
  ADD COLUMN IF NOT EXISTS invite_token VARCHAR(120),
  ADD COLUMN IF NOT EXISTS invite_expires_at TIMESTAMPTZ,
  ADD COLUMN IF NOT EXISTS activated_at TIMESTAMPTZ;

ALTER TABLE store_team_members
  DROP CONSTRAINT IF EXISTS store_team_members_user_id_unique;

ALTER TABLE store_team_members
  ADD CONSTRAINT store_team_members_user_id_unique UNIQUE (user_id);

UPDATE store_team_members stm
SET user_id = u.id
FROM users u
WHERE stm.user_id IS NULL
  AND LOWER(stm.email) = LOWER(u.email);

CREATE INDEX IF NOT EXISTS idx_store_team_members_store_status
  ON store_team_members (store_id, status);

CREATE INDEX IF NOT EXISTS idx_store_team_members_user
  ON store_team_members (user_id);
