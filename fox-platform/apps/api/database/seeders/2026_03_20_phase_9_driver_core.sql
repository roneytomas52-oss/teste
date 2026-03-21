UPDATE driver_profiles
SET
  bank_name = 'Banco Fox',
  bank_branch_number = '1524',
  bank_account_number = '99341-0',
  rating = 4.90,
  last_active_at = NOW() - INTERVAL '25 minutes',
  updated_at = NOW()
WHERE id = 'dddddddd-dddd-4ddd-8ddd-dddddddddddd';

INSERT INTO driver_documents (
  id, driver_profile_id, document_type, label, file_name, storage_path, status
)
VALUES
  (
    'd1000000-0000-4000-8000-000000000001',
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    'cnh',
    'CNH',
    'cnh-lucas-ferreira.pdf',
    '/drivers/lucas-ferreira/cnh.pdf',
    'approved'
  ),
  (
    'd1000000-0000-4000-8000-000000000002',
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    'veiculo',
    'Documento da moto',
    'documento-moto-lucas.pdf',
    '/drivers/lucas-ferreira/moto.pdf',
    'approved'
  )
ON CONFLICT (id) DO UPDATE
SET
  driver_profile_id = EXCLUDED.driver_profile_id,
  document_type = EXCLUDED.document_type,
  label = EXCLUDED.label,
  file_name = EXCLUDED.file_name,
  storage_path = EXCLUDED.storage_path,
  status = EXCLUDED.status;

INSERT INTO driver_availability_slots (
  id, driver_profile_id, weekday, starts_at, ends_at, status
)
VALUES
  ('d2000000-0000-4000-8000-000000000001', 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', 1, '11:00', '14:00', 'open'),
  ('d2000000-0000-4000-8000-000000000002', 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', 1, '18:00', '22:00', 'open'),
  ('d2000000-0000-4000-8000-000000000003', 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', 2, '11:00', '14:00', 'open'),
  ('d2000000-0000-4000-8000-000000000004', 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', 2, '18:00', '22:00', 'open'),
  ('d2000000-0000-4000-8000-000000000005', 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', 3, '18:00', '22:00', 'partial'),
  ('d2000000-0000-4000-8000-000000000006', 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', 4, '11:00', '14:00', 'open'),
  ('d2000000-0000-4000-8000-000000000007', 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', 4, '18:00', '22:00', 'open'),
  ('d2000000-0000-4000-8000-000000000008', 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', 5, '11:00', '14:00', 'open'),
  ('d2000000-0000-4000-8000-000000000009', 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', 5, '18:00', '23:00', 'open')
ON CONFLICT (id) DO UPDATE
SET
  driver_profile_id = EXCLUDED.driver_profile_id,
  weekday = EXCLUDED.weekday,
  starts_at = EXCLUDED.starts_at,
  ends_at = EXCLUDED.ends_at,
  status = EXCLUDED.status,
  updated_at = NOW();

INSERT INTO driver_wallet_transactions (
  id, driver_profile_id, order_id, direction, status, amount, description, occurred_at
)
VALUES
  (
    'd3000000-0000-4000-8000-000000000001',
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    '0a000001-0000-4000-8000-000000000004',
    'credit',
    'processed',
    9.40,
    'Corrida #RUN-8741 concluida',
    NOW() - INTERVAL '2 hours'
  ),
  (
    'd3000000-0000-4000-8000-000000000002',
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    '0a000001-0000-4000-8000-000000000003',
    'credit',
    'processed',
    7.90,
    'Corrida #RUN-8739 concluida',
    NOW() - INTERVAL '3 hours'
  ),
  (
    'd3000000-0000-4000-8000-000000000003',
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    NULL,
    'credit',
    'scheduled',
    1268.00,
    'Repasse previsto para o fechamento semanal',
    NOW() + INTERVAL '3 days'
  ),
  (
    'd3000000-0000-4000-8000-000000000004',
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    NULL,
    'debit',
    'under_review',
    12.10,
    'Ajuste em analise por distancia divergente',
    NOW() - INTERVAL '1 day'
  )
ON CONFLICT (id) DO UPDATE
SET
  driver_profile_id = EXCLUDED.driver_profile_id,
  order_id = EXCLUDED.order_id,
  direction = EXCLUDED.direction,
  status = EXCLUDED.status,
  amount = EXCLUDED.amount,
  description = EXCLUDED.description,
  occurred_at = EXCLUDED.occurred_at;
