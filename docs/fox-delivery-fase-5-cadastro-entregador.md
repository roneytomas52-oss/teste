# Fox Delivery - Fase 5: Jornada de Cadastro do Entregador

Data: 2026-03-18
Status: Concluida
Escopo: Evolucao da jornada de `cadastro-entregador.php` para onboarding progressivo do entregador parceiro.

## Objetivo da fase
Transformar o cadastro do entregador em uma experiencia progressiva, com etapas claras, stepper visual e o mesmo processamento de dados ja integrado ao backend do projeto.

## Entregas realizadas
### 1. Jornada do entregador reorganizada por etapas
O fluxo do entregador passou a seguir cinco etapas:
- Perfil
- Identificacao
- Operacao
- Documentos
- Confirmacao

### 2. Stepper visual proprio para o entregador
Foi criado um stepper dedicado para a jornada operacional do entregador, com indicacao de:
- etapa atual
- etapas concluidas
- navegacao entre passos

### 3. Formulario progressivo sem descartar a logica atual
O formulario do entregador continua usando:
- zonas de atendimento
- modalidades / veiculos cadastrados
- validacao do backend atual
- envio para a API ja existente

Ou seja, a evolucao aconteceu no frontend, sem perder a estrutura de processamento ja integrada.

### 4. Nova distribuicao dos campos
#### Etapa 1
- primeiro nome
- sobrenome
- e-mail
- telefone
- senha

#### Etapa 2
- tipo de documento
- numero do documento
- codigo de referencia

#### Etapa 3
- zona de atendimento
- modelo de remuneracao
- modalidade / veiculo

#### Etapa 4
- foto do entregador
- imagens do documento

#### Etapa 5
- resumo do que segue para analise
- envio final para conferencia operacional

### 5. Validacao por etapa
Foi adicionado comportamento de wizard para o entregador, com:
- validacao do passo atual antes de avancar
- retorno para a etapa anterior
- abertura automatica da primeira etapa invalida no envio
- atualizacao visual do stepper conforme o progresso

### 6. Shell da pagina alinhado ao fluxo real
O topo da pagina de cadastro do entregador agora reflete as etapas reais da jornada, em vez de usar um stepper generico.

## Arquivos alterados
- `fox-landing-standalone/includes/registration_portal.php`
- `fox-landing-standalone/assets/style.css`

## Criterios de aceite atendidos
- A jornada do entregador deixou de ser um formulario unico cansativo.
- O usuario consegue entender em que etapa esta.
- O fluxo ficou mais simples que o da loja, sem perder organizacao.
- O backend e o processamento atual foram preservados.
- A experiencia visual do entregador agora acompanha o mesmo padrao profissional da loja.

## Observacoes
- A selecao de modalidade do entregador foi incorporada na etapa operacional, junto com zona e remuneracao.
- A validacao final do envio continua protegida pelo processamento do backend.
- Validacao em `php -l` nao foi executada porque o binario `php` nao esta disponivel neste terminal.

## Proxima fase
Fase 6 - Suporte, ajuda e paginas institucionais
