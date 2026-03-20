CREATE INDEX idx_user_roles_user_id
  ON user_roles (user_id);

CREATE INDEX idx_user_roles_role_id
  ON user_roles (role_id);

CREATE INDEX idx_role_permissions_role_id
  ON role_permissions (role_id);

CREATE INDEX idx_refresh_sessions_user_id_active
  ON refresh_sessions (user_id, expires_at)
  WHERE revoked_at IS NULL;

CREATE INDEX idx_refresh_sessions_token_hash_active
  ON refresh_sessions (refresh_token_hash)
  WHERE revoked_at IS NULL;

CREATE INDEX idx_audit_logs_actor_user_id
  ON audit_logs (actor_user_id, created_at DESC);

CREATE INDEX idx_domain_events_name
  ON domain_events (event_name, occurred_at DESC);
