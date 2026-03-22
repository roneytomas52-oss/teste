INSERT INTO store_team_members (
  id, store_id, user_id, invited_by_user_id, invite_token, invite_expires_at, activated_at, full_name, email, phone, role_slug, status, permissions, last_login_at
)
VALUES
  (
    'f1000000-0000-4000-8000-000000000001',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '44444444-4444-4444-8444-444444444444',
    '22222222-2222-4222-8222-222222222222',
    'invite-manager-fox-burgers',
    NOW() + INTERVAL '7 days',
    NOW() - INTERVAL '10 days',
    'Mariana Costa',
    'mariana.costa@foxburgers.com.br',
    '(11) 97777-2201',
    'manager',
    'active',
    '["orders","catalog","inventory","finance","team"]'::jsonb,
    NOW() - INTERVAL '3 hours'
  ),
  (
    'f1000000-0000-4000-8000-000000000002',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '55555555-5555-4555-8555-555555555555',
    '22222222-2222-4222-8222-222222222222',
    'invite-catalog-fox-burgers',
    NOW() + INTERVAL '7 days',
    NOW() - INTERVAL '4 days',
    'Rafael Martins',
    'rafael.martins@foxburgers.com.br',
    '(11) 97777-2202',
    'catalog',
    'active',
    '["catalog","inventory"]'::jsonb,
    NOW() - INTERVAL '1 day'
  ),
  (
    'f1000000-0000-4000-8000-000000000003',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '66666666-6666-4666-8666-666666666666',
    '22222222-2222-4222-8222-222222222222',
    'invite-finance-fox-burgers',
    NOW() + INTERVAL '7 days',
    NULL,
    'Beatriz Oliveira',
    'beatriz.oliveira@foxburgers.com.br',
    '(11) 97777-2203',
    'finance',
    'invited',
    '["finance","reports"]'::jsonb,
    NULL
  )
ON CONFLICT (id) DO UPDATE
SET
  user_id = EXCLUDED.user_id,
  invited_by_user_id = EXCLUDED.invited_by_user_id,
  invite_token = EXCLUDED.invite_token,
  invite_expires_at = EXCLUDED.invite_expires_at,
  activated_at = EXCLUDED.activated_at,
  full_name = EXCLUDED.full_name,
  email = EXCLUDED.email,
  phone = EXCLUDED.phone,
  role_slug = EXCLUDED.role_slug,
  status = EXCLUDED.status,
  permissions = EXCLUDED.permissions,
  last_login_at = EXCLUDED.last_login_at,
  updated_at = NOW();
