CREATE TABLE password_reset_tokens (
  email VARCHAR(160) PRIMARY KEY,
  token_hash CHAR(64) NOT NULL,
  expires_at TIMESTAMPTZ NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_password_reset_tokens_expires_at
  ON password_reset_tokens (expires_at);
