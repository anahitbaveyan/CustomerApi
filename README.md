# Customer API

This project provides a RESTful API for managing customers and their accounts. It allows users to create, update, delete customers, and perform operations on their accounts such as deposits, withdrawals, and transfers.

## API Endpoints

### Customers
- **GET /customers**: Retrieve a list of all customers.
- **POST /customers**: Create a new customer.
- **GET /customers/{id}**: Retrieve details of a customer by their ID.
- **PUT /customers/{id}**: Update details of a specific customer.
- **DELETE /customers/{id}**: Remove a customer.

### Accounts
- **GET /accounts/{id}**: Retrieve the balance of a specific account.
- **POST /accounts/{id}/deposit**: Deposit funds into an account.
- **POST /accounts/{id}/withdraw**: Withdraw funds from an account.
- **POST /accounts/transfer**: Transfer funds between two accounts.

## Setup and Installation

### Prerequisites
Make sure you have Docker installed on your system.

### Step 1: Clone the Repository
First, clone the repository and navigate into the project directory:

### Step 2: Creat a .env file
cd .env.example.env
update the .env with your environment configuration 

APP_NAME=CustomerAPI
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=customer_apis
DB_USERNAME=DB_USERNAME
DB_PASSWORD=DB_PASSWORD


### Step 3 : Build and Start the containers:

docker-compose up -d

### Step 4: Run Database Migrations
docker exec -it customersApis_app php artisan migrate

### Step 5: Run Tests
docker exec -it customersApis_app php artisan test


