INSERT INTO orders (
  id, store_id, driver_profile_id, order_number, customer_name, customer_phone, customer_address,
  status, payment_method, payment_status, subtotal, delivery_fee, total, placed_at, accepted_at, completed_at
)
VALUES
  (
    '0a000001-0000-4000-8000-000000000001',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    NULL,
    'FD-10482',
    'Ana Souza',
    '+55 11 98888-1001',
    'Rua das Palmeiras, 120 - Sao Paulo/SP',
    'pending_acceptance',
    'online_card',
    'paid',
    72.90,
    6.00,
    78.90,
    NOW() - INTERVAL '8 minutes',
    NULL,
    NULL
  ),
  (
    '0a000001-0000-4000-8000-000000000002',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    'FD-10479',
    'Paulo Lima',
    '+55 11 98888-1002',
    'Av. Central, 450 - Sao Paulo/SP',
    'preparing',
    'pix',
    'paid',
    48.20,
    6.00,
    54.20,
    NOW() - INTERVAL '18 minutes',
    NOW() - INTERVAL '15 minutes',
    NULL
  ),
  (
    '0a000001-0000-4000-8000-000000000003',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    'FD-10471',
    'Julia Martins',
    '+55 11 98888-1003',
    'Rua do Parque, 88 - Sao Paulo/SP',
    'ready_for_pickup',
    'online_card',
    'paid',
    25.00,
    6.00,
    31.00,
    NOW() - INTERVAL '24 minutes',
    NOW() - INTERVAL '22 minutes',
    NULL
  ),
  (
    '0a000001-0000-4000-8000-000000000004',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    'FD-10455',
    'Carlos Vieira',
    '+55 11 98888-1004',
    'Rua Aurora, 900 - Sao Paulo/SP',
    'completed',
    'online_card',
    'paid',
    86.40,
    6.00,
    92.40,
    NOW() - INTERVAL '65 minutes',
    NOW() - INTERVAL '58 minutes',
    NOW() - INTERVAL '18 minutes'
  ),
  (
    '0a000001-0000-4000-8000-000000000005',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    NULL,
    'FD-10433',
    'Mariana Costa',
    '+55 11 98888-1005',
    'Rua dos Ipes, 32 - Sao Paulo/SP',
    'cancelled',
    'cash',
    'pending',
    39.90,
    5.00,
    44.90,
    NOW() - INTERVAL '130 minutes',
    NULL,
    NULL
  )
ON CONFLICT (id) DO UPDATE
SET
  store_id = EXCLUDED.store_id,
  driver_profile_id = EXCLUDED.driver_profile_id,
  order_number = EXCLUDED.order_number,
  customer_name = EXCLUDED.customer_name,
  customer_phone = EXCLUDED.customer_phone,
  customer_address = EXCLUDED.customer_address,
  status = EXCLUDED.status,
  payment_method = EXCLUDED.payment_method,
  payment_status = EXCLUDED.payment_status,
  subtotal = EXCLUDED.subtotal,
  delivery_fee = EXCLUDED.delivery_fee,
  total = EXCLUDED.total,
  placed_at = EXCLUDED.placed_at,
  accepted_at = EXCLUDED.accepted_at,
  completed_at = EXCLUDED.completed_at,
  updated_at = NOW();

INSERT INTO order_items (id, order_id, product_id, product_name, quantity, unit_price, total_price, notes)
VALUES
  ('0b000001-0000-4000-8000-000000000001', '0a000001-0000-4000-8000-000000000001', '88888888-8888-4888-8888-888888888888', 'Hamburguer Fox Prime', 2, 34.90, 69.80, 'Sem cebola'),
  ('0b000001-0000-4000-8000-000000000002', '0a000001-0000-4000-8000-000000000001', 'bbbbbbbb-9999-4999-8999-999999999999', 'Agua 500ml', 1, 3.10, 3.10, NULL),
  ('0b000001-0000-4000-8000-000000000003', '0a000001-0000-4000-8000-000000000002', '99999999-9999-4999-8999-999999999999', 'Combo mercado essencial', 1, 48.20, 48.20, NULL),
  ('0b000001-0000-4000-8000-000000000004', '0a000001-0000-4000-8000-000000000003', 'aaaaaaaa-9999-4999-8999-999999999999', 'Kit farmacia rapida', 1, 25.00, 25.00, NULL),
  ('0b000001-0000-4000-8000-000000000005', '0a000001-0000-4000-8000-000000000004', '88888888-8888-4888-8888-888888888888', 'Hamburguer Fox Prime', 2, 43.20, 86.40, 'Entrega em portaria'),
  ('0b000001-0000-4000-8000-000000000006', '0a000001-0000-4000-8000-000000000005', 'bbbbbbbb-9999-4999-8999-999999999999', 'Agua 500ml', 2, 19.95, 39.90, NULL)
ON CONFLICT (id) DO NOTHING;

INSERT INTO order_status_logs (id, order_id, previous_status, next_status, actor_user_id, note, created_at)
VALUES
  ('0c000001-0000-4000-8000-000000000001', '0a000001-0000-4000-8000-000000000001', NULL, 'pending_acceptance', '22222222-2222-4222-8222-222222222222', 'Pedido entrou na fila da loja.', NOW() - INTERVAL '8 minutes'),
  ('0c000001-0000-4000-8000-000000000002', '0a000001-0000-4000-8000-000000000002', NULL, 'accepted', '22222222-2222-4222-8222-222222222222', 'Pedido aceito automaticamente.', NOW() - INTERVAL '15 minutes'),
  ('0c000001-0000-4000-8000-000000000003', '0a000001-0000-4000-8000-000000000002', 'accepted', 'preparing', '22222222-2222-4222-8222-222222222222', 'Item em preparo.', NOW() - INTERVAL '12 minutes'),
  ('0c000001-0000-4000-8000-000000000004', '0a000001-0000-4000-8000-000000000003', NULL, 'accepted', '22222222-2222-4222-8222-222222222222', 'Pedido aceito.', NOW() - INTERVAL '22 minutes'),
  ('0c000001-0000-4000-8000-000000000005', '0a000001-0000-4000-8000-000000000003', 'accepted', 'preparing', '22222222-2222-4222-8222-222222222222', 'Preparacao iniciada.', NOW() - INTERVAL '19 minutes'),
  ('0c000001-0000-4000-8000-000000000006', '0a000001-0000-4000-8000-000000000003', 'preparing', 'ready_for_pickup', '22222222-2222-4222-8222-222222222222', 'Aguardando coleta.', NOW() - INTERVAL '6 minutes'),
  ('0c000001-0000-4000-8000-000000000007', '0a000001-0000-4000-8000-000000000004', NULL, 'accepted', '22222222-2222-4222-8222-222222222222', 'Pedido aceito.', NOW() - INTERVAL '58 minutes'),
  ('0c000001-0000-4000-8000-000000000008', '0a000001-0000-4000-8000-000000000004', 'accepted', 'preparing', '22222222-2222-4222-8222-222222222222', 'Preparacao iniciada.', NOW() - INTERVAL '50 minutes'),
  ('0c000001-0000-4000-8000-000000000009', '0a000001-0000-4000-8000-000000000004', 'preparing', 'ready_for_pickup', '22222222-2222-4222-8222-222222222222', 'Pedido pronto.', NOW() - INTERVAL '38 minutes'),
  ('0c000001-0000-4000-8000-000000000010', '0a000001-0000-4000-8000-000000000004', 'ready_for_pickup', 'on_route', '11111111-1111-4111-8111-111111111111', 'Entregador em rota.', NOW() - INTERVAL '28 minutes'),
  ('0c000001-0000-4000-8000-000000000011', '0a000001-0000-4000-8000-000000000004', 'on_route', 'completed', '11111111-1111-4111-8111-111111111111', 'Pedido finalizado.', NOW() - INTERVAL '18 minutes'),
  ('0c000001-0000-4000-8000-000000000012', '0a000001-0000-4000-8000-000000000005', NULL, 'cancelled', '11111111-1111-4111-8111-111111111111', 'Pedido cancelado por indisponibilidade.', NOW() - INTERVAL '112 minutes')
ON CONFLICT (id) DO NOTHING;
