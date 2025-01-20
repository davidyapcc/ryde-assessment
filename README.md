# User Management API

A Laravel-based RESTful API for user management with authentication using Laravel Sanctum.

## Features

- User Authentication (Register, Login, Logout)
- User Management (CRUD operations)
- API Documentation with Swagger/OpenAPI
- Token-based Authentication with Sanctum
- Request Validation
- Consistent JSON Response Format
- Pagination Support

## Requirements

- PHP 8.3+
- Composer
- MySQL 8.0+
- Laravel 11.0+

## Installation

1. Clone the repository:
```bash
git clone <git@github.com:davidyapcc/ryde-assessment.git>
cd ryde-assessment
```

2. Install dependencies:
```bash
composer install
```

3. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations:
```bash
php artisan migrate
```

6. Generate Swagger documentation:
```bash
php artisan l5-swagger:generate
```

## Running the Application

1. Start the development server:
```bash
php artisan serve
```

2. Access the API documentation:
- UI: http://127.0.0.1:8000/api/documentation
- JSON: http://127.0.0.1:8000/docs/api-docs.json

## API Endpoints

### Authentication
- POST `/api/auth/register` - Register a new user
- POST `/api/auth/login` - Login user
- POST `/api/auth/logout` - Logout user (requires authentication)

### User Management
- GET `/api/users` - List all users (paginated)
- POST `/api/users` - Create a new user
- GET `/api/users/{id}` - Get user details
- PUT `/api/users/{id}` - Update user
- DELETE `/api/users/{id}` - Delete user

## Architecture

### Directory Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php    # Handles authentication (register, login, logout)
│   │       └── UserController.php    # Handles user CRUD operations
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── LoginRequest.php     # Login request validation
│   │   │   └── RegisterRequest.php  # Registration request validation
│   │   ├── ListUsersRequest.php     # User listing and pagination validation
│   │   ├── StoreUserRequest.php     # User creation validation
│   │   └── UpdateUserRequest.php    # User update validation
│   └── Resources/
│       └── UserResource.php         # User resource transformation
├── Models/
│   └── User.php                     # User model with Sanctum authentication
└── Providers/                       # Service providers
```

### Key Components

1. **Controllers**
   - `AuthController`: Handles user authentication
   - `UserController`: Manages CRUD operations for users

2. **Form Requests**
   - Validate incoming requests
   - Define validation rules
   - Handle authorization

3. **Resources**
   - Transform models into JSON responses
   - Control data exposure
   - Format consistent responses

4. **Authentication**
   - Uses Laravel Sanctum for token-based auth
   - Secure routes with middleware
   - Token management

## Error Handling

The API returns consistent error responses:

```json
{
    "status": "error",
    "message": "Error message here",
    "errors": {
        "field": ["Error details"]
    }
}
```

HTTP status codes:
- 200: Success
- 201: Created
- 401: Unauthorized
- 404: Not Found
- 422: Validation Error
- 500: Server Error

## Development

1. Generate API documentation:
```bash
php artisan l5-swagger:generate
```

2. Clear application cache:
```bash
php artisan optimize:clear
```

## Testing

Run the test suite:

```bash
php artisan test
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
