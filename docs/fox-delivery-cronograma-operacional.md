# Fox Delivery - Cronograma Operacional de Execucao

Data-base: 2026-03-18
Status geral: Execucao em andamento
Escopo: Camada publica da `fox-landing-standalone`, preservando backend e integracoes validas sempre que possivel.
Proxima fase operacional: Ciclo de refinamento orientado por validacao real

## Fonte de verdade
Este documento passa a ser a referencia operacional do projeto.

Regras de uso:
- Nao iniciar uma nova fase sem concluir, revisar e aprovar a fase anterior.
- Nao repetir exploracao ja concluida sem motivo tecnico claro.
- Toda mudanca relevante deve ser refletida neste documento ao final da fase.
- O frontend publico pode ser refeito do zero; backend e integracoes so devem ser alterados quando houver necessidade comprovada.
- Artes, imagens e efeitos visuais entram apenas depois da estrutura, copy e arquitetura estarem aprovadas.

## Registro do que ja foi feito
### Concluido
- Benchmark estrutural dos sites publicos do iFood para cliente, parceiro e entregador.
- Blueprint estrategico da Fox Delivery com arquitetura recomendada por jornada.
- Definicao de que a Fox Delivery deve operar com funis separados para cliente, parceiro e entregador.
- Confirmacao de que a camada publica atual pode ser reconstruida do zero sem descartar automaticamente o backend existente.
- Fase 1 concluida: arquitetura-base, navegacao principal, componentes compartilhados e direcao de copy consolidados.
- Fase 2 concluida: `index.php` reestruturado como home de conversao e hub de jornadas.
- Fase 3 concluida: `cadastro-parceiros.php` reestruturado como pagina de decisao entre loja e entregador.
- Fase 4 concluida: jornada de `cadastro-loja.php` convertida em onboarding progressivo por etapas.
- Fase 5 concluida: jornada de `cadastro-entregador.php` convertida em onboarding progressivo por etapas.
- Fase 6 concluida: ajuda, como funciona, contato institucional e footer reforcado na camada publica.
- Fase 7 concluida: revisao final de copy, consistencia, shell visual e instrumentacao basica de eventos.

### Ja existente no projeto
- Home publica: `fox-landing-standalone/index.php`
- Landing de entrada de cadastro: `fox-landing-standalone/cadastro-parceiros.php`
- Cadastro de loja: `fox-landing-standalone/cadastro-loja.php`
- Cadastro de entregador: `fox-landing-standalone/cadastro-entregador.php`
- Estilos centralizados em `fox-landing-standalone/assets/style.css`

### Observacoes importantes
- O estado visual atual nao deve ser tratado como versao final de marca.
- A base de arquitetura, copy e fluxos ja foi consolidada.
- Os proximos ganhos tendem a vir de validacao real, refinamento visual premium, analytics e performance.

## Ordem obrigatoria de execucao
1. Fundacao e direcao do produto
2. Home (`index.php`)
3. Landing de segmentacao (`cadastro-parceiros.php`)
4. Cadastro de loja (`cadastro-loja.php`)
5. Cadastro de entregador (`cadastro-entregador.php`)
6. Ajuda, como funciona, institucional e suporte
7. Qualidade final, conversao e validacao

## Quadro de fases
| Fase | Nome | Status inicial | Dependencia | Saida obrigatoria |
| --- | --- | --- | --- | --- |
| 0 | Descoberta e benchmark | Concluida | Nenhuma | Benchmark + blueprint |
| 1 | Fundacao da experiencia Fox Delivery | Concluida | Fase 0 | Mapa de paginas, navegacao, componentes, copy-base |
| 2 | Home de conversao | Concluida | Fase 1 | `index.php` refeito com UX de hub |
| 3 | Landing de segmentacao de cadastro | Concluida | Fase 2 | `cadastro-parceiros.php` refeito como pagina de decisao |
| 4 | Jornada de cadastro da loja | Concluida | Fase 3 | `cadastro-loja.php` com fluxo progressivo |
| 5 | Jornada de cadastro do entregador | Concluida | Fase 3 | `cadastro-entregador.php` com fluxo progressivo |
| 6 | Suporte, ajuda e paginas institucionais | Concluida | Fases 2 a 5 | FAQ, ajuda, como funciona, footer institucional |
| 7 | QA, refinamento e instrumentacao | Concluida | Fases 2 a 6 | Revisao final, consistencia, conversao e checklist |

## Fase 1 - Fundacao da experiencia Fox Delivery
Status: Concluida

Objetivo:
Definir a base da nova experiencia publica da Fox Delivery antes de redesenhar telas isoladas.

Entregaveis:
- Arquitetura de paginas e navegacao principal.
- Mapa de jornada por publico: cliente, parceiro e entregador.
- Sistema base de componentes: header, hero, card, CTA, FAQ, stepper, footer.
- Direcao de copy institucional e comercial.
- Regras de uso de cores, espacos, botoes e hierarquia visual.

Arquivos previstos:
- `fox-landing-standalone/includes/layout.php`
- `fox-landing-standalone/assets/style.css`
- Documento de apoio, se necessario

Criterios de aceite:
- Ficar definido o papel de cada pagina.
- Ficar definido o que entra e o que nao entra em cada hero.
- Ficar definido o padrao visual que sera repetido no site.
- Ficar definido o tom oficial de copy da Fox Delivery.

Nao fazer nesta fase:
- Refino visual pesado com imagens finais.
- Geracao de artes.
- Insercao de efeitos sem funcao de conversao.

## Fase 2 - Home de conversao (`index.php`)
Status: Concluida

Objetivo:
Transformar a home em um hub de decisao e conversao para cliente, parceiro e entregador.

Entregaveis:
- Header limpo e coerente com a arquitetura final.
- Hero curto, direto e forte.
- Boxes principais por categoria.
- Bloco "Como funciona".
- Entrada clara para parceiros.
- Entrada clara para entregadores.
- FAQ curta.
- Footer institucional completo.

Criterios de aceite:
- A home deve explicar a Fox Delivery em poucos segundos.
- O usuario deve entender para onde ir sem ler blocos longos.
- Os boxes devem ser claramente clicaveis e funcionais.
- Nao pode haver mencao a plataformas de terceiros.

Dependencias:
- Fase 1 aprovada

## Fase 3 - Landing de segmentacao (`cadastro-parceiros.php`)
Status: Concluida

Objetivo:
Criar uma pagina de decisao que direcione corretamente para cadastro de loja ou cadastro de entregador.

Entregaveis:
- Hero comercial claro.
- Dois cards principais: loja e entregador.
- Bloco de beneficios da plataforma.
- Bloco de orientacao sobre analise e aprovacao.
- FAQ comercial curta.
- CTA final consistente.

Criterios de aceite:
- A pagina nao pode parecer formulario.
- A pagina deve ajudar o usuario a escolher sua jornada.
- Os cards devem ter hierarquia clara e alta conversao.

Dependencias:
- Fase 2 aprovada

## Fase 4 - Jornada de cadastro da loja (`cadastro-loja.php`)
Status: Concluida

Objetivo:
Estruturar o cadastro da loja como onboarding progressivo, profissional e confiavel.

Entregaveis:
- Hero curto da jornada.
- Stepper de progresso.
- Etapas logicas: dados da loja, responsavel, operacao, documentos, confirmacao.
- Validacao visual clara.
- Integracao preservada com backend oficial, quando aplicavel.

Criterios de aceite:
- O formulario nao pode ser um bloco unico cansativo.
- O usuario deve entender em que etapa esta.
- Campos e regras devem estar alinhados ao fluxo administrativo.

Dependencias:
- Fase 3 aprovada

## Fase 5 - Jornada de cadastro do entregador (`cadastro-entregador.php`)
Status: Concluida

Objetivo:
Estruturar o cadastro do entregador como fluxo operacional por modalidade e validacao.

Entregaveis:
- Hero curto e objetivo.
- Escolha inicial de modalidade, se aplicavel.
- Stepper de progresso.
- Etapas de dados pessoais, documentacao, veiculo/modalidade, regiao e envio.
- Integracao preservada com backend oficial, quando aplicavel.

Criterios de aceite:
- O fluxo deve ser mais simples que o da loja, mas igualmente profissional.
- Requisitos e documentos devem ficar claros.
- A experiencia deve transmitir organizacao e seriedade operacional.

Dependencias:
- Fase 3 aprovada

Resultado da fase:
- `cadastro-entregador.php` passou a operar com jornada progressiva em cinco etapas.
- O stepper visual do shell agora reflete o fluxo operacional do entregador.
- O processamento atual e a integracao com o backend foram preservados.

## Fase 6 - Suporte, ajuda e institucional
Status: Concluida

Objetivo:
Dar confianca de plataforma real com paginas de apoio e suporte.

Entregaveis:
- FAQ estruturada.
- Pagina "Como funciona".
- Blocos institucionais e de operacao.
- Footer institucional completo.
- Canais de contato e orientacao ao usuario.

Arquivos provaveis:
- `fox-landing-standalone/contato.php`
- `fox-landing-standalone/sobre.php`
- Novas paginas, se necessario

Criterios de aceite:
- O usuario deve conseguir entender a operacao sem depender do suporte humano.
- O site deve parecer empresa organizada em nivel nacional.

Dependencias:
- Fases 2, 3, 4 e 5 aprovadas

Resultado da fase:
- Navegacao publica ampliada com "Como funciona" e "Ajuda".
- Paginas institucionais e de suporte passaram a compor a experiencia da plataforma.
- Footer institucional reforcado para transmitir mais confianca e contexto operacional.

## Fase 7 - QA, refinamento e instrumentacao
Status: Concluida

Objetivo:
Consolidar a experiencia final, reduzir inconsistencias e preparar conversao e manutencao.

Entregaveis:
- Revisao geral de copy.
- Revisao visual desktop e mobile.
- Checklist de consistencia entre paginas.
- Revisao de links, CTAs, formularios e estados.
- Medicao de eventos principais, quando aplicavel.

Criterios de aceite:
- Nao haver textos genericos nem linguagem amadora.
- Nao haver componentes destoando entre paginas.
- Nao haver elementos visuais infantis, improvisados ou desalinhados.
- O conjunto deve parecer plataforma pronta para escalar.

Dependencias:
- Fases 2 a 6 aprovadas

Resultado da fase:
- Revisao final de copy, links e consistencia entre paginas.
- Shell compartilhado ajustado com melhor aplicacao da marca e footer coerente.
- Instrumentacao basica adicionada para cliques e formularios principais.

## Criticos a evitar durante a execucao
- Pular para artes finais antes de aprovar estrutura e copy.
- Misturar jornada de parceiro com jornada de entregador na mesma tela operacional.
- Repetir mudancas cosmeticas sem resolver arquitetura.
- Inserir imagem "bonita" que nao conversa com o layout.
- Mencionar marcas concorrentes no site da Fox Delivery.
- Alterar backend sensivel sem necessidade tecnica validada.

## Checklist de passagem entre fases
Uma fase so pode ser encerrada quando:
- O objetivo da fase estiver atendido.
- Os criterios de aceite da fase estiverem atendidos.
- O impacto nas proximas fases estiver claro.
- Este documento for atualizado com o novo status.

## Estado atual registrado
Fase atual recomendada: Ciclo de refinamento orientado por validacao real

Proximo movimento recomendado:
- Validar a experiencia no navegador em desktop e mobile, revisar os eventos instrumentados e priorizar backlog de performance, analytics e refinamento visual final.
