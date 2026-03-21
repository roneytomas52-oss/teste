CREATE TABLE partner_leads (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  company_name VARCHAR(180) NOT NULL,
  contact_name VARCHAR(180) NOT NULL,
  email VARCHAR(180) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  city VARCHAR(120) NOT NULL,
  business_type VARCHAR(40) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'new',
  source VARCHAR(40) NOT NULL DEFAULT 'landing',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT partner_leads_business_type_check CHECK (business_type IN ('restaurant', 'market', 'pharmacy', 'convenience')),
  CONSTRAINT partner_leads_status_check CHECK (status IN ('new', 'qualified', 'discarded', 'converted'))
);

CREATE TABLE driver_leads (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  full_name VARCHAR(180) NOT NULL,
  email VARCHAR(180) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  city VARCHAR(120) NOT NULL,
  modal VARCHAR(40) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'new',
  source VARCHAR(40) NOT NULL DEFAULT 'landing',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT driver_leads_modal_check CHECK (modal IN ('bike', 'motorcycle', 'car')),
  CONSTRAINT driver_leads_status_check CHECK (status IN ('new', 'qualified', 'discarded', 'converted'))
);
