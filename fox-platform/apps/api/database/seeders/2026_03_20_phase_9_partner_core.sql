INSERT INTO store_hours (
  store_id, weekday, opens_at, closes_at, is_active
)
VALUES
  ('cccccccc-cccc-4ccc-8ccc-cccccccccccc', 1, '09:00', '23:00', TRUE),
  ('cccccccc-cccc-4ccc-8ccc-cccccccccccc', 2, '09:00', '23:00', TRUE),
  ('cccccccc-cccc-4ccc-8ccc-cccccccccccc', 3, '09:00', '23:00', TRUE),
  ('cccccccc-cccc-4ccc-8ccc-cccccccccccc', 4, '09:00', '23:00', TRUE),
  ('cccccccc-cccc-4ccc-8ccc-cccccccccccc', 5, '09:00', '23:00', TRUE),
  ('cccccccc-cccc-4ccc-8ccc-cccccccccccc', 6, '10:00', '23:30', TRUE),
  ('cccccccc-cccc-4ccc-8ccc-cccccccccccc', 0, '11:00', '20:00', TRUE)
ON CONFLICT (store_id, weekday) DO UPDATE
SET
  opens_at = EXCLUDED.opens_at,
  closes_at = EXCLUDED.closes_at,
  is_active = EXCLUDED.is_active,
  updated_at = NOW();

INSERT INTO store_documents (
  id, store_id, document_type, label, file_name, storage_path, status, metadata
)
VALUES (
  'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
  'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
  'cnpj',
  'Comprovante de CNPJ',
  'comprovante-cnpj.pdf',
  '/stores/cccccccc-cccc-4ccc-8ccc-cccccccccccc/cnpj/comprovante-cnpj.pdf',
  'approved',
  '{"issuer":"Receita Federal","notes":"Documento inicial validado"}'::jsonb
)
ON CONFLICT (id) DO UPDATE
SET
  document_type = EXCLUDED.document_type,
  label = EXCLUDED.label,
  file_name = EXCLUDED.file_name,
  storage_path = EXCLUDED.storage_path,
  status = EXCLUDED.status,
  metadata = EXCLUDED.metadata,
  updated_at = NOW();
