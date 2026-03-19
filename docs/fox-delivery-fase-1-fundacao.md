# Fox Delivery - Fase 1: Fundacao da Experiencia Publica

Data: 2026-03-18
Status: Concluida
Escopo: Definicao da arquitetura, navegacao, componentes-base, direcao visual e direcao de copy da camada publica da Fox Delivery.

## Objetivo da fase
Criar a base comum da experiencia publica antes de redesenhar home, landing de parceiros e jornadas de cadastro.

## Decisoes consolidadas
### 1. Arquitetura de paginas
Mapa principal aprovado:
- `index.php`
  Papel: home e hub de jornadas.
- `cadastro-parceiros.php`
  Papel: landing de decisao entre loja e entregador.
- `cadastro-loja.php`
  Papel: onboarding progressivo da loja.
- `cadastro-entregador.php`
  Papel: onboarding progressivo do entregador.
- `sobre.php`
  Papel: institucional leve da marca.
- `contato.php`
  Papel: ponto unico de atendimento e duvidas.

Paginas previstas para fases futuras:
- FAQ / ajuda
- Como funciona
- Planos e taxas
- Politicas e institucional ampliado

### 2. Navegacao principal
Navegacao-base da camada publica:
- Inicio
- Para parceiros
- Para entregadores
- Sobre a Fox
- Contato

Acoes fixas do topo:
- Entrar no painel
- Baixar app

Racional:
- Separar claramente cliente, parceiro e entregador.
- Tratar o painel como acao operacional, nao como item de menu principal.
- Manter a barra publica curta e direta.

### 3. Papel de cada hero
Regras aprovadas:
- Hero da home:
  deve vender a proposta de valor da Fox Delivery em poucos segundos.
- Hero da landing de parceiros:
  deve ajudar o usuario a escolher a jornada correta.
- Hero do cadastro da loja:
  deve contextualizar o fluxo, nao tentar vender a plataforma inteira.
- Hero do cadastro do entregador:
  deve contextualizar requisitos e etapa de entrada.
- Hero de paginas institucionais:
  deve ser curto, informativo e com copy clara.

O que nao entra em hero:
- FAQ
- explicacao longa
- muitos CTAs concorrentes
- listas grandes
- dashboard falso
- boxes decorativos sem funcao

### 4. Sistema de componentes-base
Componentes definidos para o site:
- `header`
  Funcao: orientacao principal e acesso ao painel/app.
- `brand`
  Funcao: assinatura institucional da marca no topo.
- `hero`
  Funcao: proposta de valor e primeira decisao.
- `card`
  Funcao: segmentacao, beneficio ou proxima acao.
- `CTA`
  Funcao: conduzir para a proxima etapa.
- `section-head`
  Funcao: abrir secoes com hierarquia clara.
- `footer`
  Funcao: confianca, jornadas e canais de atendimento.
- `stepper`
  Funcao futura: mostrar progresso em cadastros.
- `FAQ`
  Funcao futura: reduzir atrito e suporte manual.

### 5. Direcao visual
Base visual aprovada:
- vermelho como cor principal de decisao
- laranja como apoio energetico
- branco e fundos quentes claros para respiro
- cards claros com sombra suave
- botoes arredondados e firmes
- visual limpo, sem excesso de efeitos
- linguagem visual de plataforma, nao de template generico

Regras de composicao:
- mais hierarquia, menos decoracao
- muito contraste onde houver CTA
- boxes sempre com funcao
- imagens so entram quando ajudarem a vender ou orientar
- nao usar elementos infantis, improvisados ou excessivamente ilustrativos

### 6. Direcao de copy
Tom oficial aprovado:
- profissional
- comercial
- confiavel
- nacional
- direto

Deve soar como:
- plataforma estruturada
- empresa preparada para escala
- experiencia clara para operacao e atendimento

Nao deve soar como:
- promessa vaga
- landing amadora
- texto genrico de template
- linguagem infantil

Padroes de copy:
- titulos curtos e fortes
- subtitulos com contexto real
- bullets objetivos
- chamadas para acao sem exagero
- sem citar concorrentes dentro do site da Fox Delivery
- sem citar plataformas terceiras como base da marca

## Implementacao aplicada nesta fase
### Arquivos ajustados
- `fox-landing-standalone/includes/layout.php`
  Resultado:
  navegacao principal reorganizada, acoes de topo definidas e footer institucional base estruturado.

- `fox-landing-standalone/assets/style.css`
  Resultado:
  tokens de cor, sombras, superficies, tipografia-base e componentes compartilhados inicializados.

- `fox-landing-standalone/sobre.php`
  Resultado:
  copy institucional limpa e alinhada com a marca.

- `fox-landing-standalone/contato.php`
  Resultado:
  copy de atendimento reorganizada e canal de contato alinhado ao papel institucional da pagina.

## Criticos eliminados nesta fase
- Navegacao publica sem separacao clara entre jornadas.
- Footer fraco para uma plataforma com ambicao nacional.
- Copy institucional com mencao a terceiros.
- Base visual sem sistema claro para ser repetido nas proximas paginas.

## O que fica propositalmente para a fase 2 em diante
- Redesenho profundo da home.
- Nova landing de parceiros.
- Reformulacao das jornadas de cadastro.
- FAQ e ajuda estruturadas.
- Artes finais e refinamento visual pesado.

## Saida aprovada da fase
Ao final da fase 1, a Fox Delivery passa a ter:
- arquitetura-base definida
- navegacao principal definida
- tom de copy consolidado
- sistema visual-base iniciado
- paginas institucionais minimas alinhadas com a marca

## Proxima fase
Fase 2 - Home de conversao (`index.php`)
