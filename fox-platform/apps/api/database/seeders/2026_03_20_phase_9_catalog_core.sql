INSERT INTO categories (id, slug, name, status)
VALUES
  ('44444444-4444-4444-8444-444444444444', 'restaurantes', 'Restaurante', 'active'),
  ('55555555-5555-4555-8555-555555555555', 'mercado', 'Mercado', 'active'),
  ('66666666-6666-4666-8666-666666666666', 'farmacia', 'Farmácia', 'active'),
  ('77777777-7777-4777-8777-777777777777', 'conveniencia', 'Conveniência', 'active')
ON CONFLICT (slug) DO UPDATE
SET
  name = EXCLUDED.name,
  status = EXCLUDED.status,
  updated_at = NOW();

INSERT INTO products (
  id, store_id, category_id, name, description, sku, base_price, currency, status, stock_quantity, min_stock_quantity, sold_count, image_path
)
VALUES
  (
    '88888888-8888-4888-8888-888888888888',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '44444444-4444-4444-8444-444444444444',
    'Hambúrguer Fox Prime',
    'Sanduíche premium com pão brioche, carne artesanal, queijo e molho da casa.',
    'FOX-BURGER-001',
    34.90,
    'BRL',
    'active',
    38,
    10,
    84,
    '/catalog/fox-prime.jpg'
  ),
  (
    '99999999-9999-4999-8999-999999999999',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '55555555-5555-4555-8555-555555555555',
    'Combo mercado essencial',
    'Seleção de itens de mercado com alta rotatividade e entrega rápida.',
    'FOX-MERCADO-010',
    59.90,
    'BRL',
    'active',
    18,
    15,
    62,
    '/catalog/mercado-essencial.jpg'
  ),
  (
    'aaaaaaaa-9999-4999-8999-999999999999',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '66666666-6666-4666-8666-666666666666',
    'Kit farmácia rápida',
    'Itens essenciais de farmácia com foco em velocidade e praticidade.',
    'FOX-FARMA-021',
    28.70,
    'BRL',
    'active',
    4,
    12,
    37,
    '/catalog/farmacia-rapida.jpg'
  ),
  (
    'bbbbbbbb-9999-4999-8999-999999999999',
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    '77777777-7777-4777-8777-777777777777',
    'Água 500ml',
    'Item de conveniência com giro alto e reposição recorrente.',
    'FOX-CONV-100',
    5.50,
    'BRL',
    'paused',
    0,
    10,
    128,
    '/catalog/agua-500ml.jpg'
  )
ON CONFLICT (id) DO UPDATE
SET
  store_id = EXCLUDED.store_id,
  category_id = EXCLUDED.category_id,
  name = EXCLUDED.name,
  description = EXCLUDED.description,
  sku = EXCLUDED.sku,
  base_price = EXCLUDED.base_price,
  currency = EXCLUDED.currency,
  status = EXCLUDED.status,
  stock_quantity = EXCLUDED.stock_quantity,
  min_stock_quantity = EXCLUDED.min_stock_quantity,
  sold_count = EXCLUDED.sold_count,
  image_path = EXCLUDED.image_path,
  updated_at = NOW();
