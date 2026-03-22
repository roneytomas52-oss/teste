CREATE TABLE approval_reviews (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  entity_type VARCHAR(20) NOT NULL,
  entity_id UUID NOT NULL,
  decision VARCHAR(20) NOT NULL DEFAULT 'note',
  note TEXT,
  actor_user_id UUID REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT approval_reviews_entity_type_check CHECK (entity_type IN ('partner', 'driver')),
  CONSTRAINT approval_reviews_decision_check CHECK (decision IN ('approve', 'reject', 'note'))
);

CREATE INDEX idx_approval_reviews_entity
  ON approval_reviews (entity_type, entity_id, created_at DESC);
