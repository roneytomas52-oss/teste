INSERT INTO users (
  id, full_name, email, phone, password_hash, status, locale
)
VALUES
  (
    '77777777-7777-4777-8777-777777777777',
    'Patricia Lima',
    'patricia.lima@foxmercado.com.br',
    '+55 21 98888-1101',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'pending',
    'pt_BR'
  ),
  (
    '88888888-8888-4888-8888-888888888888',
    'Carlos Souza',
    'carlos.souza@foxdriver.com.br',
    '+55 31 97777-8801',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'pending',
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

INSERT INTO partner_accounts (
  id, owner_user_id, legal_name, document_number, status
)
VALUES (
  '99999999-9999-4999-8999-999999999999',
  '77777777-7777-4777-8777-777777777777',
  'Fox Mercado Sul Ltda',
  '98.765.432/0001-55',
  'pending'
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
  'aaaaaaaa-1111-4aaa-8aaa-aaaaaaaa1111',
  '99999999-9999-4999-8999-999999999999',
  'Fox Mercado Pinheiros',
  'Fox Mercado Sul Ltda',
  '98.765.432/0001-55',
  'patricia.lima@foxmercado.com.br',
  '+55 21 98888-1101',
  'pending',
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

INSERT INTO store_documents (
  id, store_id, document_type, label, file_name, storage_path, status, metadata
)
VALUES
  (
    'aaaa0000-1111-4000-8000-000000000001',
    'aaaaaaaa-1111-4aaa-8aaa-aaaaaaaa1111',
    'cnpj',
    'Comprovante de CNPJ',
    'cnpj-fox-mercado-pinheiros.pdf',
    '/stores/fox-mercado-pinheiros/cnpj.pdf',
    'approved',
    '{"issuer":"Receita Federal","notes":"Documento fiscal validado"}'::jsonb
  ),
  (
    'aaaa0000-1111-4000-8000-000000000002',
    'aaaaaaaa-1111-4aaa-8aaa-aaaaaaaa1111',
    'alvara',
    'Alvara de funcionamento',
    'alvara-fox-mercado-pinheiros.pdf',
    '/stores/fox-mercado-pinheiros/alvara.pdf',
    'pending',
    '{"issuer":"Prefeitura","notes":"Aguardando confirmacao da vigencia"}'::jsonb
  )
ON CONFLICT (id) DO UPDATE
SET
  store_id = EXCLUDED.store_id,
  document_type = EXCLUDED.document_type,
  label = EXCLUDED.label,
  file_name = EXCLUDED.file_name,
  storage_path = EXCLUDED.storage_path,
  status = EXCLUDED.status,
  metadata = EXCLUDED.metadata,
  updated_at = NOW();

INSERT INTO driver_profiles (
  id, user_id, modal, status, city, state
)
VALUES (
  'bbbbbbbb-1111-4bbb-8bbb-bbbbbbbb1111',
  '88888888-8888-4888-8888-888888888888',
  'bike',
  'pending',
  'Belo Horizonte',
  'MG'
)
ON CONFLICT (user_id) DO UPDATE
SET
  modal = EXCLUDED.modal,
  status = EXCLUDED.status,
  city = EXCLUDED.city,
  state = EXCLUDED.state,
  updated_at = NOW();

INSERT INTO driver_documents (
  id, driver_profile_id, document_type, label, file_name, storage_path, status
)
VALUES
  (
    'bbbb0000-1111-4000-8000-000000000001',
    'bbbbbbbb-1111-4bbb-8bbb-bbbbbbbb1111',
    'identidade',
    'Documento de identidade',
    'identidade-carlos-souza.pdf',
    '/drivers/carlos-souza/identidade.pdf',
    'approved'
  ),
  (
    'bbbb0000-1111-4000-8000-000000000002',
    'bbbbbbbb-1111-4bbb-8bbb-bbbbbbbb1111',
    'cadastro',
    'Comprovante de cadastro',
    'cadastro-carlos-souza.pdf',
    '/drivers/carlos-souza/cadastro.pdf',
    'pending'
  )
ON CONFLICT (id) DO UPDATE
SET
  driver_profile_id = EXCLUDED.driver_profile_id,
  document_type = EXCLUDED.document_type,
  label = EXCLUDED.label,
  file_name = EXCLUDED.file_name,
  storage_path = EXCLUDED.storage_path,
  status = EXCLUDED.status;

INSERT INTO user_roles (
  id, user_id, role_id
)
SELECT gen_random_uuid(), '77777777-7777-4777-8777-777777777777', r.id
FROM roles r
WHERE r.slug = 'partner_owner'
ON CONFLICT DO NOTHING;

INSERT INTO user_roles (
  id, user_id, role_id
)
SELECT gen_random_uuid(), '88888888-8888-4888-8888-888888888888', r.id
FROM roles r
WHERE r.slug = 'driver'
ON CONFLICT DO NOTHING;

INSERT INTO approval_reviews (
  id, entity_type, entity_id, decision, note, actor_user_id
)
VALUES
  (
    'cccc0000-1111-4000-8000-000000000001',
    'partner',
    'aaaaaaaa-1111-4aaa-8aaa-aaaaaaaa1111',
    'note',
    'Fila criada para revisao documental da loja e validacao do alvara.',
    '11111111-1111-4111-8111-111111111111'
  ),
  (
    'cccc0000-1111-4000-8000-000000000002',
    'driver',
    'bbbbbbbb-1111-4bbb-8bbb-bbbbbbbb1111',
    'note',
    'Cadastro aguardando confirmacao do comprovante complementar do entregador.',
    '11111111-1111-4111-8111-111111111111'
  )
ON CONFLICT (id) DO UPDATE
SET
  entity_type = EXCLUDED.entity_type,
  entity_id = EXCLUDED.entity_id,
  decision = EXCLUDED.decision,
  note = EXCLUDED.note,
  actor_user_id = EXCLUDED.actor_user_id,
  updated_at = NOW();
