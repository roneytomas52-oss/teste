INSERT INTO users (
  id, full_name, email, phone, password_hash, status, locale
)
VALUES
  (
    '77777777-7777-4777-8777-777777777777',
    'Carla Nascimento',
    'carla.nascimento@foxplatform.com',
    '+55 11 98888-2201',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'active',
    'pt_BR'
  ),
  (
    '88888888-8888-4888-8888-888888888888',
    'Juliana Prado',
    'juliana.prado@foxplatform.com',
    '+55 11 98888-2202',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'active',
    'pt_BR'
  ),
  (
    '99999999-9999-4999-8999-999999999999',
    'Fabio Ribeiro',
    'fabio.ribeiro@foxplatform.com',
    '+55 11 98888-2203',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'suspended',
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
VALUES
  (
    'a7000000-0000-4000-8000-000000000001',
    '77777777-7777-4777-8777-777777777777',
    'Operacao',
    FALSE
  ),
  (
    'a7000000-0000-4000-8000-000000000002',
    '88888888-8888-4888-8888-888888888888',
    'Financeiro',
    FALSE
  ),
  (
    'a7000000-0000-4000-8000-000000000003',
    '99999999-9999-4999-8999-999999999999',
    'Suporte',
    FALSE
  )
ON CONFLICT (user_id) DO UPDATE
SET
  department = EXCLUDED.department,
  is_super = EXCLUDED.is_super,
  updated_at = NOW();

INSERT INTO user_roles (
  id, user_id, role_id
)
SELECT gen_random_uuid(), '77777777-7777-4777-8777-777777777777', r.id
FROM roles r
WHERE r.slug = 'admin_operacional'
ON CONFLICT DO NOTHING;

INSERT INTO user_roles (
  id, user_id, role_id
)
SELECT gen_random_uuid(), '88888888-8888-4888-8888-888888888888', r.id
FROM roles r
WHERE r.slug = 'admin_financeiro'
ON CONFLICT DO NOTHING;

INSERT INTO user_roles (
  id, user_id, role_id
)
SELECT gen_random_uuid(), '99999999-9999-4999-8999-999999999999', r.id
FROM roles r
WHERE r.slug = 'suporte'
ON CONFLICT DO NOTHING;

INSERT INTO notifications (
  id, scope, user_id, level, context, title, body, action_label, action_url, is_read, created_at
)
VALUES
  (
    'f7000000-0000-4000-8000-000000000001',
    'admin',
    '11111111-1111-4111-8111-111111111111',
    'warning',
    'access',
    'Equipe administrativa atualizada',
    'Novos perfis internos foram habilitados e exigem revisao do escopo de acesso.',
    'Abrir permissoes',
    './permissions.html',
    FALSE,
    NOW() - INTERVAL '35 minutes'
  ),
  (
    'f7000000-0000-4000-8000-000000000002',
    'admin',
    '77777777-7777-4777-8777-777777777777',
    'info',
    'access',
    'Acesso operacional liberado',
    'Seu perfil administrativo foi criado e ja pode acompanhar pedidos, suporte e aprovacoes.',
    'Abrir dashboard',
    './index.html',
    FALSE,
    NOW() - INTERVAL '15 minutes'
  ),
  (
    'f7000000-0000-4000-8000-000000000003',
    'admin',
    '88888888-8888-4888-8888-888888888888',
    'success',
    'finance',
    'Perfil financeiro configurado',
    'O acesso do time financeiro foi concluido com permissao para leitura e exportacao.',
    'Abrir financeiro',
    './finance.html',
    FALSE,
    NOW() - INTERVAL '10 minutes'
  )
ON CONFLICT (id) DO UPDATE
SET
  level = EXCLUDED.level,
  context = EXCLUDED.context,
  title = EXCLUDED.title,
  body = EXCLUDED.body,
  action_label = EXCLUDED.action_label,
  action_url = EXCLUDED.action_url,
  is_read = EXCLUDED.is_read,
  created_at = EXCLUDED.created_at;
