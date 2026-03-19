# Fox Delivery - Fase 6: Suporte, Ajuda e Paginas Institucionais

Data: 2026-03-18
Status: Concluida
Escopo: Estruturacao da camada institucional e de suporte da `fox-landing-standalone`.

## Objetivo da fase
Dar confianca de plataforma real com paginas de apoio, base de ajuda, orientacao de funcionamento e reforco institucional da navegacao publica.

## Entregas realizadas
### 1. Nova pagina "Como funciona"
Foi criada a pagina `como-funciona.php` para explicar:
- as jornadas principais da Fox Delivery
- a separacao entre cliente, parceiro e entregador
- a logica operacional da plataforma
- os proximos caminhos dentro da experiencia publica

### 2. Nova pagina "Ajuda"
Foi criada a pagina `ajuda.php` com:
- temas principais da central de ajuda
- base inicial de perguntas frequentes
- orientacoes sobre cadastro, analise e contato
- chamada final para o canal de atendimento

### 3. Reforco institucional das paginas existentes
As paginas `sobre.php` e `contato.php` foram ampliadas para transmitir mais:
- posicionamento da plataforma
- pilares da experiencia Fox Delivery
- contexto de atendimento e triagem
- leitura comercial, operacional e institucional

### 4. Navegacao principal atualizada
O header passou a incluir:
- Inicio
- Como funciona
- Para parceiros
- Para entregadores
- Ajuda
- Contato

### 5. Footer institucional ampliado
O footer foi reorganizado para cobrir:
- plataforma
- jornadas
- empresa
- atendimento

Isso melhora a confianca institucional e reduz a sensacao de landing incompleta.

### 6. Integracao da ajuda nas jornadas existentes
As paginas `index.php` e `cadastro-parceiros.php` passaram a apontar tambem para a nova central de ajuda, integrando suporte e jornada em vez de tratar ajuda como area isolada.

## Arquivos alterados
- `fox-landing-standalone/includes/layout.php`
- `fox-landing-standalone/assets/style.css`
- `fox-landing-standalone/index.php`
- `fox-landing-standalone/cadastro-parceiros.php`
- `fox-landing-standalone/sobre.php`
- `fox-landing-standalone/contato.php`
- `fox-landing-standalone/como-funciona.php`
- `fox-landing-standalone/ajuda.php`

## Criterios de aceite atendidos
- A plataforma agora possui pagina de ajuda.
- A plataforma agora possui pagina de explicacao de funcionamento.
- O footer ficou mais institucional e completo.
- O site passou a parecer empresa organizada, e nao apenas conjunto de landing pages.
- O suporte e a orientacao foram integrados ao fluxo publico da Fox Delivery.

## Observacoes
- A fase focou em estrutura, copy e arquitetura institucional.
- Nao houve validacao com `php -l` porque o binario `php` nao esta disponivel neste terminal.

## Proxima fase
Fase 7 - QA, refinamento e instrumentacao
