# Agenda Laravel API

Esta é uma aplicação de API de agenda construída com Laravel. Esta documentação fornece as instruções necessárias para configurar e rodar a aplicação.

## Pré-requisitos

- Docker
- Docker Compose

## Configuração Inicial

1. **Criar e Configurar o Arquivo `.env`**

   Crie o arquivo `.env` a partir do exemplo e configure as variáveis de ambiente:

   ```bash
   cp .env.example .env
   ```

   Edite o arquivo `.env` e configure as variáveis de ambiente, especialmente as configurações do banco de dados:

   ```env
   APP_NAME="Agenda API"
   APP_ENV=local
   APP_KEY=base64:WgNJySLlFkersvG5bsFg6tP/R1kzrMiBlWksntKHoL8=
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   LOG_CHANNEL=stack
   LOG_DEPRECATIONS_CHANNEL=null
   LOG_LEVEL=debug

   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=agenda_api
   DB_USERNAME=agenda
   DB_PASSWORD=agenda

   BROADCAST_DRIVER=log
   CACHE_DRIVER=file
   FILESYSTEM_DISK=local
   QUEUE_CONNECTION=sync
   SESSION_DRIVER=file
   SESSION_LIFETIME=120

   SANCTUM_STATEFUL_DOMAINS=localhost:8000
   ```

2. **Subir os Containers Docker**

   Inicie os containers usando o comando abaixo:

   ```bash
   docker-compose up -d
   ```

3. **Instalar Dependências**

   Instale as dependências do Composer:

   ```bash
   docker exec -it agenda-api composer install
   ```

4. **Gerar a Chave da Aplicação**

   Gere a chave da aplicação:

   ```bash
   docker exec -it agenda-api php artisan key:generate
   ```

5. **Rodar as Migrações e Seeders**

   Execute as migrações para criar as tabelas no banco de dados e popule-as com dados iniciais usando os seeders:

   ```bash
   docker exec -it agenda-api php artisan migrate --seed
   ```

## Acessar a Aplicação

- A aplicação estará disponível em: [http://localhost:8000](http://localhost:8000)
- A documentação Swagger estará disponível em: [http://localhost:8000/api/v1/documentation](http://localhost:8000/api/v1/documentation)

## Testando a API

1. **Executar todos os testes**

   Execute os testes:

   ```bash
   docker exec -it agenda-api php artisan test
   ```

2. **Fazer Login e Obter o Token**

   Use `curl` ou Postman com os dados abaixo para fazer login e obter um token:

   ```bash
   curl -X POST http://localhost:8000/api/v1/login \
   -H "Content-Type: application/json" \
   -d '{
      "email": "john.doe@domain.com",
      "password": "password"
   }'
   ```

3. **Usar o Token para Acessar Rotas Protegidas**

   Use o token obtido para acessar as rotas protegidas, por exemplo, para criar uma atividade:

   ```bash
   curl -X POST http://localhost:8000/api/v1/activities \
   -H "Content-Type: application/json" \
   -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
   -d '{
     "title": "Meeting",
     "type": "Work",
     "description": "Project meeting",
     "user_id": 1,
     "start_date": "2023-05-01 10:00:00",
     "end_date": "2023-05-01 11:00:00"
   }'
   ```

## Conclusão

Seguindo esses passos, você deve ser capaz de configurar, rodar e testar a API de agenda desenvolvida com Laravel. Se tiver algum problema durante o processo, verifique os logs do Laravel para obter mais detalhes sobre o que pode estar errado.