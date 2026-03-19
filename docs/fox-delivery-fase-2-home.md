# Fox Delivery - Fase 2: Home de Conversao

Data: 2026-03-18
Status: Concluida
Escopo: Reestruturacao da `index.php` como hub de jornadas da Fox Delivery.

## Objetivo da fase
Transformar a home em uma pagina de decisao e conversao, com hierarquia clara para cliente final, parceiros e entregadores.

## Entregas realizadas
### 1. Hero da home refeito
- Hero anterior substituido por um bloco mais curto, objetivo e institucional.
- Removida a dependencia da imagem de banner como elemento central da decisao.
- Hero agora apresenta:
  - proposta de valor da Fox Delivery
  - CTA para explorar categorias
  - CTA para cadastro de operacao
  - links de download do app
  - resumo das jornadas da plataforma

### 2. Home reorganizada como hub
Blocos principais definidos:
- hero principal
- categorias da plataforma
- como funciona
- entradas para parceiros e entregadores
- FAQ curta

### 3. Boxes principais por categoria
Categorias estruturadas em cards:
- Restaurantes
- Mercado
- Farmacia
- Conveniencia
- Entregas urbanas
- Novas operacoes

Objetivo dos cards:
- mostrar amplitude da plataforma
- aumentar escaneabilidade
- sustentar percepcao de operacao nacional

### 4. Entrada clara para parceiros e entregadores
Dois cards dedicados foram criados na home:
- `Quero vender na Fox Delivery`
- `Quero entregar com a Fox Delivery`

Racional:
- a home nao concentra cadastro
- ela distribui o usuario para a jornada certa

### 5. FAQ curta de orientacao
Foi criada uma FAQ inicial com perguntas objetivas para:
- explicar o que o usuario encontra na plataforma
- indicar onde cadastrar loja
- indicar onde iniciar a jornada de entregador
- apontar para o contato da equipe

### 6. Ajustes de base visual da home
No `style.css`, a home recebeu:
- hero proprio da pagina
- grid especifico para cards de categoria
- grid especifico para jornadas
- grid especifico para FAQ
- composicao visual consistente com a fundacao definida na fase 1

## Arquivos alterados
- `fox-landing-standalone/index.php`
- `fox-landing-standalone/assets/style.css`

## Criterios de aceite atendidos
- A home explica a Fox Delivery rapidamente.
- O usuario entende para onde ir sem ler blocos longos.
- Os boxes principais estao organizados por funcao.
- Existem entradas claras para parceiros e entregadores.
- Nao ha mencao a plataformas terceiras na home.

## Observacoes
- A home foi tratada como pagina de produto, nao como vitrine decorativa.
- Imagens finais e refinamento artistico pesado continuam fora do escopo desta fase.
- Validacao em `php -l` nao foi executada porque o binario `php` nao esta disponivel no terminal deste ambiente.

## Proxima fase
Fase 3 - Landing de segmentacao (`cadastro-parceiros.php`)
