INSERT INTO support_tickets (
  id, scope, partner_account_id, store_id, driver_profile_id, created_by_user_id, channel, assigned_team, priority, status, subject, description, last_message_at
)
VALUES
  (
    'e1000000-0000-4000-8000-000000000001',
    'partner',
    'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    NULL,
    '22222222-2222-4222-8222-222222222222',
    'operations',
    'operacao',
    'high',
    'in_progress',
    'Revisar tempo estimado no horario de pico do almoco',
    'Precisamos ampliar em 5 minutos a janela de retirada entre 11h30 e 13h30 para reduzir cancelamentos no pico.',
    NOW() - INTERVAL '18 minutes'
  ),
  (
    'e1000000-0000-4000-8000-000000000002',
    'partner',
    'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    NULL,
    '22222222-2222-4222-8222-222222222222',
    'finance',
    'financeiro',
    'normal',
    'answered',
    'Conferencia de divergencia no repasse da campanha de mercado',
    'Solicitamos revisao do repasse referente a campanha de mercado da semana passada.',
    NOW() - INTERVAL '1 day'
  ),
  (
    'e1000000-0000-4000-8000-000000000003',
    'driver',
    NULL,
    NULL,
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    '33333333-3333-4333-8333-333333333333',
    'documents',
    'cadastro',
    'normal',
    'resolved',
    'Validacao de documento concluida com sucesso',
    'A validacao do documento principal foi concluida e o perfil segue apto para operacao.',
    NOW() - INTERVAL '2 hours'
  ),
  (
    'e1000000-0000-4000-8000-000000000004',
    'driver',
    NULL,
    NULL,
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    '33333333-3333-4333-8333-333333333333',
    'earnings',
    'financeiro',
    'high',
    'in_progress',
    'Ajuste de corrida com distancia divergente',
    'Preciso revisar uma corrida com distancia divergente no repasse da semana.',
    NOW() - INTERVAL '42 minutes'
  )
ON CONFLICT (id) DO UPDATE
SET
  scope = EXCLUDED.scope,
  partner_account_id = EXCLUDED.partner_account_id,
  store_id = EXCLUDED.store_id,
  driver_profile_id = EXCLUDED.driver_profile_id,
  created_by_user_id = EXCLUDED.created_by_user_id,
  channel = EXCLUDED.channel,
  assigned_team = EXCLUDED.assigned_team,
  priority = EXCLUDED.priority,
  status = EXCLUDED.status,
  subject = EXCLUDED.subject,
  description = EXCLUDED.description,
  last_message_at = EXCLUDED.last_message_at,
  updated_at = NOW();

INSERT INTO support_messages (
  id, ticket_id, sender_user_id, sender_role, body
)
VALUES
  (
    'e2000000-0000-4000-8000-000000000001',
    'e1000000-0000-4000-8000-000000000001',
    '22222222-2222-4222-8222-222222222222',
    'partner_owner',
    'Precisamos ampliar em 5 minutos a janela de retirada entre 11h30 e 13h30 para reduzir cancelamentos no pico.'
  ),
  (
    'e2000000-0000-4000-8000-000000000002',
    'e1000000-0000-4000-8000-000000000002',
    '22222222-2222-4222-8222-222222222222',
    'partner_owner',
    'Solicitamos revisao do repasse referente a campanha de mercado da semana passada.'
  ),
  (
    'e2000000-0000-4000-8000-000000000003',
    'e1000000-0000-4000-8000-000000000003',
    '33333333-3333-4333-8333-333333333333',
    'driver',
    'Gostaria de confirmar se a validacao do documento principal foi concluida.'
  ),
  (
    'e2000000-0000-4000-8000-000000000004',
    'e1000000-0000-4000-8000-000000000004',
    '33333333-3333-4333-8333-333333333333',
    'driver',
    'Preciso revisar uma corrida com distancia divergente no repasse da semana.'
  )
ON CONFLICT (id) DO UPDATE
SET
  ticket_id = EXCLUDED.ticket_id,
  sender_user_id = EXCLUDED.sender_user_id,
  sender_role = EXCLUDED.sender_role,
  body = EXCLUDED.body;
