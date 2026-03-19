# Fox Delivery - Fase 4: Jornada de Cadastro da Loja

Data: 2026-03-18
Status: Concluida
Escopo: Evolucao da jornada de `cadastro-loja.php` para onboarding progressivo da loja parceira.

## Objetivo da fase
Transformar o cadastro da loja em uma experiencia progressiva, com etapas claras, stepper visual e o mesmo processamento de dados ja integrado ao backend do projeto.

## Entregas realizadas
### 1. Jornada da loja reorganizada por etapas
O fluxo da loja passou a seguir cinco etapas:
- Responsavel
- Dados da loja
- Operacao
- Documentos
- Confirmacao

### 2. Stepper visual do onboarding
Foi criado um stepper proprio para a loja, com indicacao de:
- etapa atual
- etapas concluidas
- navegacao entre passos

### 3. Formulario progressivo sem perder integracao
O formulario da loja continua usando:
- catalogo de zonas
- modulos por zona
- planos comerciais
- pacotes
- validacao do backend atual
- envio para a API ja existente

Ou seja, a evolucao foi feita na experiencia do frontend, sem descartar a logica de processamento ja implementada.

### 4. Nova distribuicao dos campos
#### Etapa 1
- dados do responsavel legal
- e-mail
- telefone
- senha

#### Etapa 2
- nome da loja
- endereco
- zona
- modulo
- zonas de coleta quando necessario

#### Etapa 3
- latitude
- longitude
- prazo minimo
- prazo maximo
- unidade do prazo
- plano comercial
- pacote de assinatura, quando aplicavel

#### Etapa 4
- logo
- capa
- CNPJ / TIN
- validade fiscal
- certificado fiscal

#### Etapa 5
- resumo do que sera analisado
- envio final para analise

### 5. Validacao visual mais clara
Foram adicionados ajustes de validacao no frontend para melhorar a experiencia:
- senha com minimo de 8 caracteres no proprio campo
- latitude e longitude em campos numericos
- verificacao de prazo maximo maior ou igual ao minimo
- obrigatoriedade dinamica para zonas de coleta
- obrigatoriedade dinamica para pacote quando o plano exige assinatura

### 6. Shell da pagina alinhado a jornada real
O topo da pagina de cadastro da loja agora reflete as etapas reais do onboarding, em vez de usar um stepper generico.

## Arquivos alterados
- `fox-landing-standalone/includes/registration_portal.php`
- `fox-landing-standalone/assets/style.css`

## Criterios de aceite atendidos
- A jornada da loja deixou de ser um bloco unico cansativo.
- O usuario consegue entender em que etapa esta.
- As etapas seguem logica coerente de onboarding.
- O backend e o processamento atual foram preservados.
- A experiencia visual da loja ficou mais clara e profissional.

## Observacoes
- A jornada do entregador ainda nao foi convertida para esse mesmo padrao progressivo; isso fica para a fase 5.
- A validacao final do envio continua protegida pelo processamento do backend.
- Validacao em `php -l` nao foi executada porque o binario `php` nao esta disponivel neste terminal.

## Proxima fase
Fase 5 - Jornada de cadastro do entregador (`cadastro-entregador.php`)
