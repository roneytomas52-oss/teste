# Fox Delivery - Fase 3: Landing de Segmentacao de Cadastro

Data: 2026-03-18
Status: Concluida
Escopo: Reestruturacao da `cadastro-parceiros.php` como pagina de decisao entre loja e entregador.

## Objetivo da fase
Transformar a pagina de parceiros em uma landing de decisao, separando a escolha da jornada do formulario principal.

## Entregas realizadas
### 1. Hero comercial refeito
- Hero anterior substituido por uma abertura institucional e comercial.
- A pagina agora deixa claro que:
  - existe uma jornada para loja
  - existe uma jornada para entregador
  - a escolha acontece antes do cadastro principal

### 2. Dois cards principais de decisao
Foram estruturados dois cards centrais:
- `Cadastrar minha loja`
- `Quero ser entregador`

Cada card passou a ter:
- papel claro
- contexto de uso
- lista objetiva de pontos principais
- tags de apoio
- CTA proprio para a jornada correta

### 3. Bloco de beneficios da segmentacao
Foi criado um bloco explicando por que a pagina de decisao existe:
- direcionamento correto
- mais clareza operacional
- melhor experiencia de entrada

### 4. Bloco de orientacao sobre analise e aprovacao
Foi adicionada uma secao com a sequencia:
- recebimento do cadastro
- conferencia das informacoes
- orientacao de ativacao

Objetivo:
- reduzir inseguranca
- explicar o que acontece depois do envio
- dar tom mais profissional ao processo

### 5. FAQ comercial curta
Foi criada uma FAQ inicial para:
- explicar quando escolher loja
- explicar quando escolher entregador
- esclarecer que esta pagina nao e o formulario final
- apontar para contato quando houver duvida

### 6. CTA final de fechamento
Foi incluido um bloco final reforcando a escolha da jornada correta antes do cadastro principal.

## Arquivos alterados
- `fox-landing-standalone/cadastro-parceiros.php`
- `fox-landing-standalone/assets/style.css`

## Criterios de aceite atendidos
- A pagina nao parece formulario.
- A pagina ajuda o usuario a escolher sua jornada.
- Loja e entregador ficaram claramente separados.
- A orientacao sobre analise e proximos passos ficou clara.
- A pagina segue a mesma base visual e de copy da Fase 1 e da Fase 2.

## Observacoes
- A pagina foi alinhada ao papel definido no blueprint: decisao antes do onboarding.
- O cadastro completo continua nas paginas dedicadas de loja e entregador.
- Validacao em `php -l` nao foi executada porque o binario `php` nao esta disponivel neste terminal.

## Proxima fase
Fase 4 - Jornada de cadastro da loja (`cadastro-loja.php`)
