INSERT INTO users (
  id, full_name, email, phone, password_hash, status, locale
)
VALUES
  (
    '11111111-1111-4111-8111-111111111111',
    'Admin Fox Platform',
    'admin@foxplatform.com',
    '+55 11 90000-0001',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'active',
    'pt_BR'
  ),
  (
    '22222222-2222-4222-8222-222222222222',
    'Parceiro Fox Delivery',
    'parceiro@foxdelivery.com.br',
    '+55 11 90000-0002',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'active',
    'pt_BR'
  ),
  (
    '33333333-3333-4333-8333-333333333333',
    'Entregador Fox Delivery',
    'entregador@foxdelivery.com.br',
    '+55 11 90000-0003',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'active',
    'pt_BR'
  )
ON CONFLICT (email) DO UPDATE
SET
  full_name = EXCLUDED.full_name,
  phone = EXCLUDED.phone,
  password_hash = EXCLUDED.password_hash,
  status = EXCLUDED.status,
  locale = EXCLUDED.locale,
  updated_at = NOW();

INSERT INTO admin_profiles (
  id, user_id, department, is_super
)
VALUES (
  'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
  '11111111-1111-4111-8111-111111111111',
  'Plataforma',
  TRUE
)
ON CONFLICT (user_id) DO UPDATE
SET
  department = EXCLUDED.department,
  is_super = EXCLUDED.is_super,
  updated_at = NOW();

INSERT INTO partner_accounts (
  id, owner_user_id, legal_name, document_number, status
)
VALUES (
  'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
  '22222222-2222-4222-8222-222222222222',
  'Fox Partner Foods Ltda',
  '12.345.678/0001-99',
  'active'
)
ON CONFLICT (id) DO UPDATE
SET
  owner_user_id = EXCLUDED.owner_user_id,
  legal_name = EXCLUDED.legal_name,
  document_number = EXCLUDED.document_number,
  status = EXCLUDED.status,
  updated_at = NOW();

INSERT INTO stores (
  id, partner_account_id, trade_name, legal_name, document_number, email, phone, status, city, state, country
)
VALUES (
  'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
  'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
  'Fox Burgers Centro',
  'Fox Partner Foods Ltda',
  '12.345.678/0001-99',
  'parceiro@foxdelivery.com.br',
  '+55 11 90000-0002',
  'active',
  'Sao Paulo',
  'SP',
  'Brasil'
)
ON CONFLICT (id) DO UPDATE
SET
  partner_account_id = EXCLUDED.partner_account_id,
  trade_name = EXCLUDED.trade_name,
  legal_name = EXCLUDED.legal_name,
  document_number = EXCLUDED.document_number,
  email = EXCLUDED.email,
  phone = EXCLUDED.phone,
  status = EXCLUDED.status,
  city = EXCLUDED.city,
  state = EXCLUDED.state,
  country = EXCLUDED.country,
  updated_at = NOW();

INSERT INTO driver_profiles (
  id, user_id, modal, status, city, state
)
VALUES (
  'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
  '33333333-3333-4333-8333-333333333333',
  'moto',
  'active',
  'Sao Paulo',
  'SP'
)
ON CONFLICT (user_id) DO UPDATE
SET
  modal = EXCLUDED.modal,
  status = EXCLUDED.status,
  city = EXCLUDED.city,
  state = EXCLUDED.state,
  updated_at = NOW();

INSERT INTO user_roles (
  id, user_id, role_id
)
SELECT gen_random_uuid(), '11111111-1111-4111-8111-111111111111', r.id
FROM roles r
WHERE r.slug = 'super_admin'
ON CONFLICT DO NOTHING;

INSERT INTO user_roles (
  id, user_id, role_id
)
SELECT gen_random_uuid(), '22222222-2222-4222-8222-222222222222', r.id
FROM roles r
WHERE r.slug = 'partner_owner'
ON CONFLICT DO NOTHING;

INSERT INTO user_roles (
  id, user_id, role_id
)
SELECT gen_random_uuid(), '33333333-3333-4333-8333-333333333333', r.id
FROM roles r
WHERE r.slug = 'driver'
ON CONFLICT DO NOTHING;
