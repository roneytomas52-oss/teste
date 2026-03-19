# Fox Delivery - Fase 7: QA, Refinamento e Instrumentacao

Data: 2026-03-18
Status: Concluida
Escopo: Revisao final da camada publica da `fox-landing-standalone`, com foco em consistencia, acabamento e medicao basica de eventos.

## Objetivo da fase
Consolidar a experiencia publica da Fox Delivery, corrigir inconsistencias residuais e preparar a base para manutencao, refinamento visual futuro e leitura inicial de comportamento.

## Entregas realizadas
### 1. Revisao de consistencia entre paginas
Foi feita revisao da navegacao publica e das paginas principais para garantir:
- alinhamento de copy entre home, parceiros, ajuda, como funciona, sobre e contato
- integracao da area de ajuda nas jornadas da plataforma
- links coerentes entre entrada de parceiros, entregadores e suporte

### 2. Ajustes de shell visual
Foram corrigidos pontos de QA no shell compartilhado:
- aplicacao mais segura da logo no header, evitando corte excessivo da imagem
- grade do footer ajustada para refletir corretamente o numero de blocos visiveis
- reforco de consistencia entre header, footer e paginas institucionais

### 3. Refinos de copy e UX
Foram aplicados ajustes finos em:
- CTA da home para ficar mais claro
- placeholders do formulario de contato
- textos de apoio nas paginas institucionais
- links de suporte dentro da home e da landing de parceiros

### 4. Instrumentacao basica da camada publica
Foi adicionada uma instrumentacao leve, sem depender de plataforma externa neste momento:
- eventos de clique em header e footer
- eventos dos principais CTAs da home
- eventos dos CTAs da landing de parceiros
- eventos dos callouts institucionais
- inicio e envio dos formularios de contato, loja e entregador

Os eventos passam a ser enviados para:
- `window.dataLayer`
- evento customizado `fox:track`
- buffer local em `sessionStorage` para inspecao rapida da sessao

### 5. Revisao do cronograma operacional
O cronograma passou a refletir a conclusao da fase 7, removendo estados antigos e registrando o novo momento do projeto.

## Arquivos alterados
- `fox-landing-standalone/includes/layout.php`
- `fox-landing-standalone/assets/style.css`
- `fox-landing-standalone/index.php`
- `fox-landing-standalone/cadastro-parceiros.php`
- `fox-landing-standalone/contato.php`
- `fox-landing-standalone/sobre.php`
- `fox-landing-standalone/como-funciona.php`
- `fox-landing-standalone/ajuda.php`
- `fox-landing-standalone/includes/registration_portal.php`
- `docs/fox-delivery-cronograma-operacional.md`

## Checklist da fase
- Copy principal revisada
- Navegacao principal revisada
- Footer institucional revisado
- Links entre jornadas revisados
- Formulario de contato revisado
- Onboarding de loja com instrumentacao
- Onboarding de entregador com instrumentacao
- Eventos principais preparados para medicao

## Observacoes
- A instrumentacao atual e propositalmente neutra e sem dependencia de fornecedor.
- A proxima camada natural de evolucao e conectar esses eventos a uma ferramenta analitica real, quando a stack de medicao estiver definida.
- Nao houve validacao com `php -l` porque o binario `php` nao esta disponivel neste terminal.

## Estado ao final da fase
Todas as fases do cronograma operacional foram executadas.

## Proximo passo sugerido
Entrar em ciclo de refinamento orientado por negocio, com:
- validacao manual desktop e mobile em navegador
- definicao da ferramenta de analytics
- refinamento visual premium por arte final e imagem
- backlog de performance, SEO e conversao
