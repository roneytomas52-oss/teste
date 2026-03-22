INSERT INTO roles (slug, scope, name, description)
VALUES
  ('super_admin', 'admin', 'Super Admin', 'Controle total da plataforma'),
  ('admin_operacional', 'admin', 'Admin Operacional', 'Opera pedidos e aprovações'),
  ('admin_financeiro', 'admin', 'Admin Financeiro', 'Opera repasses e visão financeira'),
  ('admin_comercial', 'admin', 'Admin Comercial', 'Opera parceiros e jornadas comerciais'),
  ('suporte', 'admin', 'Suporte', 'Atende chamados e suporte operacional'),
  ('partner_owner', 'partner', 'Dono da loja', 'Gestão total da operação parceira'),
  ('partner_manager', 'partner', 'Gerente da loja', 'Gestão parcial da operação'),
  ('partner_staff', 'partner', 'Funcionário da loja', 'Operação restrita por permissão'),
  ('driver', 'driver', 'Entregador', 'Perfil operacional do entregador')
ON CONFLICT (slug) DO NOTHING;

INSERT INTO permissions (slug, module, action, name, description)
VALUES
  ('dashboard.view', 'dashboard', 'view', 'Ver dashboard', 'Acesso ao dashboard do contexto'),
  ('orders.manage', 'orders', 'manage', 'Gerir pedidos', 'Alterar e acompanhar pedidos'),
  ('catalog.manage', 'catalog', 'manage', 'Gerir catálogo', 'Criar e editar produtos'),
  ('inventory.manage', 'inventory', 'manage', 'Gerir estoque', 'Ajustar disponibilidade de itens'),
  ('finance.view', 'finance', 'view', 'Ver financeiro', 'Consultar dados financeiros'),
  ('finance.export', 'finance', 'export', 'Exportar financeiro', 'Exportar relatórios financeiros'),
  ('store.manage', 'store', 'manage', 'Gerir loja', 'Editar perfil e operação da loja'),
  ('team.manage', 'team', 'manage', 'Gerir equipe', 'Criar e editar usuários de loja'),
  ('support.manage', 'support', 'manage', 'Gerir atendimento', 'Atender chamados e tickets'),
  ('reports.view', 'reports', 'view', 'Ver relatórios', 'Consultar relatórios operacionais'),
  ('approvals.approve', 'approvals', 'approve', 'Aprovar cadastros', 'Aprovar parceiros e entregadores'),
  ('settings.manage', 'settings', 'manage', 'Gerir configurações', 'Editar parâmetros da plataforma')
ON CONFLICT (slug) DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'orders.manage',
  'catalog.manage',
  'inventory.manage',
  'finance.view',
  'finance.export',
  'store.manage',
  'team.manage',
  'support.manage',
  'reports.view',
  'approvals.approve',
  'settings.manage'
)
WHERE r.slug = 'super_admin'
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'orders.manage',
  'support.manage',
  'approvals.approve',
  'reports.view'
)
WHERE r.slug = 'admin_operacional'
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'finance.view',
  'finance.export',
  'reports.view'
)
WHERE r.slug = 'admin_financeiro'
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'store.manage',
  'support.manage',
  'reports.view'
)
WHERE r.slug = 'admin_comercial'
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'support.manage'
)
WHERE r.slug = 'suporte'
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'orders.manage',
  'catalog.manage',
  'inventory.manage',
  'finance.view',
  'store.manage',
  'team.manage',
  'support.manage',
  'reports.view'
)
WHERE r.slug = 'partner_owner'
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'orders.manage',
  'catalog.manage',
  'inventory.manage',
  'support.manage',
  'reports.view'
)
WHERE r.slug = 'partner_manager'
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'orders.manage'
)
WHERE r.slug = 'partner_staff'
ON CONFLICT DO NOTHING;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view'
)
WHERE r.slug = 'driver'
ON CONFLICT DO NOTHING;
