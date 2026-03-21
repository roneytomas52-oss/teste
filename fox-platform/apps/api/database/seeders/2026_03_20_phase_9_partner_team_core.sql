INSERT INTO store_team_members (
  id, store_id, full_name, email, phone, role_slug, status, permissions, last_login_at
)
VALUES
  (
    'f1000000-0000-4000-8000-000000000001',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
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
  full_name = EXCLUDED.full_name,
  email = EXCLUDED.email,
  phone = EXCLUDED.phone,
  role_slug = EXCLUDED.role_slug,
  status = EXCLUDED.status,
  permissions = EXCLUDED.permissions,
  last_login_at = EXCLUDED.last_login_at,
  updated_at = NOW();
