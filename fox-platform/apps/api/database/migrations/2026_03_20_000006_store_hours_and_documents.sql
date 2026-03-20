CREATE TABLE store_hours (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  store_id UUID NOT NULL REFERENCES stores(id) ON DELETE CASCADE,
  weekday SMALLINT NOT NULL,
  opens_at TIME NOT NULL,
  closes_at TIME NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT store_hours_weekday_check CHECK (weekday BETWEEN 0 AND 6)
);

CREATE UNIQUE INDEX uq_store_hours_store_weekday
  ON store_hours (store_id, weekday);

CREATE TABLE store_documents (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  store_id UUID NOT NULL REFERENCES stores(id) ON DELETE CASCADE,
  document_type VARCHAR(80) NOT NULL,
  label VARCHAR(160) NOT NULL,
  file_name VARCHAR(255),
  storage_path TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  metadata JSONB,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT store_documents_status_check CHECK (status IN ('pending', 'approved', 'rejected'))
);

CREATE INDEX idx_store_documents_store_id
  ON store_documents (store_id, status);
