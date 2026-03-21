INSERT INTO store_bank_accounts (
  id, store_id, bank_name, bank_code, branch_number, account_number, account_type,
  account_holder_name, account_holder_document, status
)
VALUES (
  'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
  'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
  'Banco Fox',
  '341',
  '1524',
  '45897-2',
  'checking',
  'Fox Partner Foods Ltda',
  '12.345.678/0001-99',
  'validated'
)
ON CONFLICT (store_id) DO UPDATE
SET
  bank_name = EXCLUDED.bank_name,
  bank_code = EXCLUDED.bank_code,
  branch_number = EXCLUDED.branch_number,
  account_number = EXCLUDED.account_number,
  account_type = EXCLUDED.account_type,
  account_holder_name = EXCLUDED.account_holder_name,
  account_holder_document = EXCLUDED.account_holder_document,
  status = EXCLUDED.status,
  updated_at = NOW();

INSERT INTO payout_requests (
  id, store_id, period_start, period_end, scheduled_for, amount, status, note
)
VALUES
  (
    'f1000000-0000-4000-8000-000000000001',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    CURRENT_DATE - INTERVAL '6 days',
    CURRENT_DATE,
    NOW() + INTERVAL '3 days',
    3182.40,
    'scheduled',
    'Repasse semanal referente aos pedidos concluidos do periodo.'
  ),
  (
    'f1000000-0000-4000-8000-000000000002',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    CURRENT_DATE - INTERVAL '13 days',
    CURRENT_DATE - INTERVAL '7 days',
    NOW() + INTERVAL '10 days',
    2940.18,
    'processing',
    'Repasse consolidado e em conferencia bancaria.'
  ),
  (
    'f1000000-0000-4000-8000-000000000003',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    CURRENT_DATE - INTERVAL '20 days',
    CURRENT_DATE - INTERVAL '14 days',
    NOW() - INTERVAL '2 days',
    4128.20,
    'completed',
    'Repasse ja liberado para a conta da loja.'
  )
ON CONFLICT (id) DO UPDATE
SET
  store_id = EXCLUDED.store_id,
  period_start = EXCLUDED.period_start,
  period_end = EXCLUDED.period_end,
  scheduled_for = EXCLUDED.scheduled_for,
  amount = EXCLUDED.amount,
  status = EXCLUDED.status,
  note = EXCLUDED.note,
  updated_at = NOW();

INSERT INTO wallet_transactions (
  id, store_id, order_id, payout_request_id, direction, transaction_type, status, description, amount, occurred_at
)
VALUES
  (
    'f2000000-0000-4000-8000-000000000001',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '0a000001-0000-4000-8000-000000000004',
    NULL,
    'credit',
    'order_revenue',
    'processed',
    'Pedidos concluidos no turno do almoco',
    1284.70,
    NOW() - INTERVAL '1 day'
  ),
  (
    'f2000000-0000-4000-8000-000000000002',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '0a000001-0000-4000-8000-000000000004',
    NULL,
    'debit',
    'platform_fee',
    'processed',
    'Taxas da plataforma e pagamentos online',
    318.42,
    NOW() - INTERVAL '1 day'
  ),
  (
    'f2000000-0000-4000-8000-000000000003',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    NULL,
    'f1000000-0000-4000-8000-000000000003',
    'debit',
    'payout',
    'sent',
    'Repasse semanal liberado',
    4128.20,
    NOW() - INTERVAL '2 days'
  ),
  (
    'f2000000-0000-4000-8000-000000000004',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '0a000001-0000-4000-8000-000000000005',
    NULL,
    'debit',
    'adjustment',
    'under_review',
    'Ajuste de cancelamento reembolsado',
    42.10,
    NOW() - INTERVAL '3 days'
  ),
  (
    'f2000000-0000-4000-8000-000000000005',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '0a000001-0000-4000-8000-000000000003',
    NULL,
    'credit',
    'order_revenue',
    'scheduled',
    'Pedidos fechados aguardando o proximo ciclo de repasse',
    3182.40,
    NOW() + INTERVAL '3 days'
  ),
  (
    'f2000000-0000-4000-8000-000000000006',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '0a000001-0000-4000-8000-000000000002',
    NULL,
    'debit',
    'refund',
    'under_review',
    'Estorno parcial em validacao do financeiro',
    18.90,
    NOW() - INTERVAL '2 days'
  )
ON CONFLICT (id) DO UPDATE
SET
  store_id = EXCLUDED.store_id,
  order_id = EXCLUDED.order_id,
  payout_request_id = EXCLUDED.payout_request_id,
  direction = EXCLUDED.direction,
  transaction_type = EXCLUDED.transaction_type,
  status = EXCLUDED.status,
  description = EXCLUDED.description,
  amount = EXCLUDED.amount,
  occurred_at = EXCLUDED.occurred_at;
