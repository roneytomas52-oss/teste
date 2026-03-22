CREATE TABLE IF NOT EXISTS platform_settings (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  group_slug VARCHAR(60) NOT NULL,
  setting_key VARCHAR(120) NOT NULL,
  value_json JSONB NOT NULL DEFAULT '{}'::jsonb,
  is_public BOOLEAN NOT NULL DEFAULT FALSE,
  updated_by_user_id UUID REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT platform_settings_group_key_unique UNIQUE (group_slug, setting_key)
);

CREATE INDEX IF NOT EXISTS idx_platform_settings_group
  ON platform_settings (group_slug);
