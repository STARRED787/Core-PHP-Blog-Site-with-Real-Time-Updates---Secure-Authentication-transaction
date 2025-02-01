# Real-time Blog System

A dynamic blog platform with real-time updates using WebSocket technology.

## Features

- Real-time blog post updates
- User authentication and authorization
- Role-based access control (Admin/User)
- CRUD operations for blog posts
- WebSocket communication
- Responsive design with Bootstrap
- XSS protection
- Secure session handling

## Technical Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, JavaScript, Bootstrap 5
- **WebSocket**: Ratchet PHP WebSocket
- **ORM**: Illuminate Database
- **Authentication**: JWT (JSON Web Tokens)

## Project Structure

blog-site/
├── auth/ # Authentication and authorization handlers
│ ├── admin_auth.php # Admin authentication middleware
│ └── user_auth.php # User authentication middleware
│
├── config/ # Configuration files
│ ├── database.php # Database connection settings
│ └── jwt_config.php # JWT token configuration
│
├── controllers/ # Business logic controllers
│ ├── BlogController.php # Blog CRUD operations
│ └── UserController.php # User management & auth
│
├── core/ # Core framework components
│ ├── Model.php # Base model class
│ └── Database.php # Database connection handler
│
├── handler/ # Request handlers
│ ├── blog_handler.php # Blog action handlers
│ ├── edit_blog.php # Blog edit handler
│ └── logout_handler.php # Logout process handler
│
├── helper/ # Helper functions and utilities
│ ├── adminAlerts.js # Admin notification system
│ └── userAlerts.js # User notification system
│
├── middleware/ # Request middleware
│ └── AuthMiddleware.php # Authentication checks
│
├── migrations/ # Database migrations
│ ├── create_users_table.php # User table schema
│ └── create_blogs_table.php # Blog table schema
│
├── models/ # Database models
│ ├── Blog.php # Blog data model
│ └── User.php # User data model
│
├── public/ # Public accessible files
│ ├── index.php # Application entry point
│ ├── css/ # Stylesheets
│ └── js/ # Client-side scripts
│
├── utils/ # Utility functions
│ └── JWTUtils.php # JWT token utilities
│
├── vendor/ # Composer dependencies
│ └── autoload.php # Autoloader
│
├── views/ # View templates
│ ├── admin.php # Admin dashboard
│ └── user.php # User dashboard
│
└── websocket/ # WebSocket components
├── server.php # WebSocket server
├── BlogServer.php # WebSocket handler
├── adminView_blog.js # Admin real-time updates
└── userView_blog.js # User real-time updates

### Directory Purposes

- **auth/**: Handles user authentication and authorization, protecting routes based on user roles
- **config/**: Contains configuration files for database, JWT, and other system settings
- **controllers/**: Contains the main business logic, handling requests and managing data flow
- **core/**: Core framework files providing basic functionality and database abstraction
- **handler/**: Request handlers for specific actions like blog operations and logout
- **helper/**: Utility functions for common tasks like displaying alerts
- **middleware/**: Request filters for authentication and other pre-processing
- **migrations/**: Database schema definitions and version control
- **models/**: Data models representing database tables with relationships
- **public/**: Publicly accessible files, entry points, and assets
- **utils/**: General utility functions and helper classes
- **vendor/**: Third-party dependencies managed by Composer
- **views/**: Template files for rendering HTML pages
- **websocket/**: Real-time communication components for live updates

## Installation

1. Clone the repository
2. Run `composer install`
3. Configure database in `config/database.php`
4. Run migrations
5. Start WebSocket server: `php websocket/server.php`

## Security Features

- JWT authentication
- Password hashing
- XSS protection
- CSRF protection
- Secure session handling
- Input validation
- SQL injection prevention

## Usage

### Admin Features
- Create, edit, and delete blog posts
- Real-time management interface
- View all blog posts in tabular format

### User Features
- View blog posts in real-time
- Responsive grid layout
- Automatic updates

## WebSocket Implementation

The system uses Ratchet PHP WebSocket server for real-time communication:
- Server runs on port 8080
- Broadcasts updates to all connected clients
- Handles reconnection automatically
- Manages client connections efficiently

## Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## License

MIT License
