INSERT INTO platform_settings (
  id, group_slug, setting_key, value_json, is_public, updated_by_user_id
)
VALUES
  (
    '70111111-1111-4111-8111-111111111111',
    'branding',
    'platform_identity',
    '{"platform_name":"Fox Delivery","support_email":"suporte@foxdelivery.com.br","support_phone":"+55 11 4000-1122"}'::jsonb,
    TRUE,
    '11111111-1111-4111-8111-111111111111'
  ),
  (
    '70222222-2222-4222-8222-222222222222',
    'operations',
    'service_rules',
    '{"default_order_sla_minutes":45,"partner_auto_approval":false,"driver_auto_approval":false}'::jsonb,
    FALSE,
    '11111111-1111-4111-8111-111111111111'
  ),
  (
    '70333333-3333-4333-8333-333333333333',
    'notifications',
    'delivery_rules',
    '{"refresh_interval_seconds":30,"partner_digest_enabled":true,"driver_digest_enabled":true}'::jsonb,
    FALSE,
    '11111111-1111-4111-8111-111111111111'
  ),
  (
    '70444444-4444-4444-8444-444444444444',
    'security',
    'session_rules',
    '{"access_token_ttl_seconds":900,"refresh_token_ttl_seconds":2592000,"password_reset_ttl_seconds":3600}'::jsonb,
    FALSE,
    '11111111-1111-4111-8111-111111111111'
  )
ON CONFLICT (group_slug, setting_key) DO UPDATE
SET
  value_json = EXCLUDED.value_json,
  is_public = EXCLUDED.is_public,
  updated_by_user_id = EXCLUDED.updated_by_user_id,
  updated_at = NOW();
