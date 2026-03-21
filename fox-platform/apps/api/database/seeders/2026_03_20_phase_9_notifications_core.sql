INSERT INTO notifications (
  id, scope, user_id, level, context, title, body, action_label, action_url, is_read, created_at
)
VALUES
  (
    'f2000000-0000-4000-8000-000000000001',
    'partner',
    '22222222-2222-4222-8222-222222222222',
    'warning',
    'orders',
    'Pedidos aguardando aceite no horario de pico',
    'A loja tem pedidos novos aguardando aceite e o SLA do almoco exige resposta mais rapida.',
    'Abrir pedidos',
    './orders.html',
    FALSE,
    NOW() - INTERVAL '10 minutes'
  ),
  (
    'f2000000-0000-4000-8000-000000000002',
    'partner',
    '22222222-2222-4222-8222-222222222222',
    'info',
    'team',
    'Novo convite de equipe pendente',
    'Existe um convite pendente para o modulo financeiro aguardando confirmacao da colaboradora.',
    'Abrir equipe',
    './team.html',
    FALSE,
    NOW() - INTERVAL '4 hours'
  ),
  (
    'f2000000-0000-4000-8000-000000000003',
    'driver',
    '33333333-3333-4333-8333-333333333333',
    'warning',
    'documents',
    'Documento do veiculo precisa de revisao',
    'Atualize a documentacao do veiculo para manter a conta liberada para operacao.',
    'Revisar documentos',
    './documents.html',
    FALSE,
    NOW() - INTERVAL '25 minutes'
  ),
  (
    'f2000000-0000-4000-8000-000000000004',
    'driver',
    '33333333-3333-4333-8333-333333333333',
    'success',
    'earnings',
    'Repasse semanal confirmado',
    'O repasse da semana foi consolidado e esta programado para o proximo ciclo bancario.',
    'Ver ganhos',
    './earnings.html',
    TRUE,
    NOW() - INTERVAL '1 day'
  ),
  (
    'f2000000-0000-4000-8000-000000000005',
    'admin',
    '11111111-1111-4111-8111-111111111111',
    'danger',
    'operations',
    'Fila de aprovacao com itens bloqueados',
    'Ha cadastros de parceiros e entregadores aguardando decisao administrativa com pendencias operacionais.',
    'Abrir fila',
    './partners-approvals.html',
    FALSE,
    NOW() - INTERVAL '30 minutes'
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
