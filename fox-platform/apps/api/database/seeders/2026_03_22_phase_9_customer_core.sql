INSERT INTO roles (slug, scope, name, description)
VALUES ('customer', 'customer', 'Cliente', 'Conta de cliente para pedidos e historico')
ON CONFLICT (slug) DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN ('dashboard.view')
WHERE r.slug = 'customer'
ON CONFLICT DO NOTHING;

INSERT INTO users (
  id, full_name, email, phone, password_hash, status, locale
)
VALUES (
  '77777777-7777-4777-8777-777777777777',
  'Cliente Fox Delivery',
  'cliente@foxdelivery.com.br',
  '+55 11 95555-0001',
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

INSERT INTO customer_profiles (
  id, user_id, city, state, marketing_opt_in
)
VALUES (
  '78787878-7878-4878-8878-787878787878',
  '77777777-7777-4777-8777-777777777777',
  'Sao Paulo',
  'SP',
  TRUE
)
ON CONFLICT (user_id) DO UPDATE
SET
  city = EXCLUDED.city,
  state = EXCLUDED.state,
  marketing_opt_in = EXCLUDED.marketing_opt_in,
  updated_at = NOW();

INSERT INTO user_roles (
  id, user_id, role_id
)
SELECT gen_random_uuid(), '77777777-7777-4777-8777-777777777777', r.id
FROM roles r
WHERE r.slug = 'customer'
ON CONFLICT DO NOTHING;
