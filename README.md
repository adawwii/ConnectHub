# ConnectHub - Real-Time Chat Application

A modern, real-time chat application built with **Laravel**, **Laravel Reverb**, **WebSockets**, and **Vite**. Features friend requests, notifications, message delivery tracking, and real-time messaging with Laravel's event-driven architecture.

## 🌟 Key Features

### Real-Time Communication
- **WebSocket Support**: Powered by Laravel Reverb for instant message delivery
- **Live Notifications**: Friend request notifications broadcast in real-time
- **Message Status Tracking**: See when messages are delivered and read
- **Sidebar Updates**: Real-time contact list synchronization

### Chat Functionality
- **Direct Messaging**: One-on-one conversations with friend contacts
- **Message Delivery States**: Sent → Delivered → Seen status tracking
- **Chat History**: Persistent message storage with full conversation history
- **Message Search**: Easy navigation through past conversations

### User Management
- **User Registration & Authentication**: Secure sign-up and login
- **Friend Request System**: Send and manage friend connections
- **Contact Management**: View and organize your contacts
- **User Profiles**: User information and online status

### Notifications System
- **Real-Time Notifications**: Instant friend request alerts via Laravel Notifications
- **Notification Management**: Accept, reject, or mark notifications as read
- **Bulk Operations**: Mark all notifications as read at once
- **Event-Driven Alerts**: Notifications triggered by application events

## 🏗️ Project Architecture

### Events (`app/Events`)
The application uses event broadcasting for real-time updates:
- **UserRegistered**: Triggered when a new user creates an account
- **FriendRequestSent**: Broadcast when friend request is initiated
- **MessageSent**: Real-time message broadcasting to recipient
- **MessageDelivered**: Confirms message arrival to sender
- **MessageSeen**: Notifies when message is read
- **SidebarUpdated**: Updates contact list for all connected users

### Controllers (`app/Http/Controllers`)
- **UserController**: Handles registration, authentication, and user lifecycle
- **ChatController**: Manages chat view and message retrieval
- **MessageController**: Sends, delivers, marks messages as seen/delivered
- **ContactsController**: Manages contacts, friend requests, and notifications

### Models (`app/Models`)
- **User**: User account with relationships to messages and contacts
- **Message**: Chat message with sender, recipient, and status tracking
- **Chat**: Conversation thread between two users
- **Contacts**: Friend relationship management

### Broadcasting Channels (`routes/channels.php`)
- Private channels for user-specific notifications
- Private channels for individual chat conversations
- Real-time channel authorization

## 🛠️ Technology Stack

| Technology | Purpose |
|---|---|
| **Laravel 12** | Backend framework & event system |
| **Laravel Reverb** | WebSocket server for real-time features |
| **WebSockets** | Bi-directional real-time communication |
| **Laravel Notifications** | Multi-channel notification delivery |
| **Eloquent ORM** | Database modeling and relationships |
| **Blade** | Server-side templating |
| **Vite** | Frontend asset bundling & development |
| **JavaScript** | Interactive client-side features |
| **PHP 8.2+** | Backend language |

## 📁 Project Structure

```
ConnectHub/
├── app/
│   ├── Events/                 # Event classes for real-time broadcasting
│   │   ├── UserRegistered.php
│   │   ├── FriendRequestSent.php
│   │   ├── MessageSent.php
│   │   ├── MessageDelivered.php
│   │   ├── MessageSeen.php
│   │   └── SidebarUpdated.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── UserController.php      # Auth & user management
│   │   │   ├── ChatController.php      # Chat UI & message retrieval
│   │   │   ├── MessageController.php   # Message operations
│   │   │   └── ContactsController.php  # Contacts & notifications
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php                    # User model with relationships
│   │   ├── Message.php                 # Message model
│   │   ├── Chat.php                    # Chat/conversation model
│   │   └── Contacts.php                # Friend relationships
│   ├── Listeners/                      # Event listeners
│   ├── Notifications/                  # Notification classes
│   ├── Services/                       # Business logic services
│   ├── Jobs/                           # Queued background jobs
│   ├── Traits/                         # Reusable code traits
│   └── Providers/
│       └── EventServiceProvider.php    # Event-listener mapping
├── bootstrap/                          # Application bootstrap
├── config/                             # Configuration files
├── database/
│   ├── migrations/                     # Database schema
│   ├── factories/                      # Model factories
│   └── seeders/                        # Database seeders
├── public/                             # Public assets
├── resources/
│   ├── views/                          # Blade templates
│   ├── js/                             # JavaScript (Vite-bundled)
│   └── css/                            # Stylesheets
├── routes/
│   ├── web.php                         # Web routes
│   └── channels.php                    # Broadcasting channels
├── storage/                            # Logs & cache
├── tests/                              # Test suite
├── vite.config.js                      # Vite configuration
└── composer.json                       # PHP dependencies
```

## 🚀 Getting Started

### Prerequisites
- PHP 8.2 or higher
- Node.js 18+ and npm
- Composer
- SQLite or MySQL database

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/adawwii/ConnectHub.git
   cd ConnectHub
   ```

2. **Run the setup script** (recommended)
   ```bash
   composer run setup
   ```

   Or manually:
   ```bash
   # Install PHP dependencies
   composer install
   
   # Copy environment file
   cp .env.example .env
   
   # Generate application key
   php artisan key:generate
   
   # Run database migrations
   php artisan migrate
   
   # Install Node dependencies
   npm install
   
   # Build frontend assets
   npm run build
   ```

3. **Configure environment** (`.env`)
   ```env
   DB_CONNECTION=sqlite
   # or MySQL
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=connecthub
   DB_USERNAME=root
   DB_PASSWORD=
   
   # Reverb WebSocket Configuration
   REVERB_APP_ID=12345
   REVERB_APP_KEY=your_key
   REVERB_APP_SECRET=your_secret
   REVERB_HOST=localhost
   REVERB_PORT=8080
   ```

## 🏃 Running the Application

### Development Mode
Start all development services (Laravel server, Reverb, queue listener, Vite):
```bash
composer run dev
```

### Individual Services
```bash
# Laravel development server (port 8000)
php artisan serve

# Reverb WebSocket server (port 8080)
php artisan reverb:start

# Listen for queued jobs
php artisan queue:listen --tries=1 --timeout=0

# Vite development server (hot reload for assets)
npm run dev

# Stream application logs
php artisan pail --timeout=0
```

### Production Build
```bash
# Build frontend assets
npm run build

# Start Reverb in production
php artisan reverb:start --host=0.0.0.0 --port=8080
```

## 📡 API Endpoints

### Authentication
- `GET /user/register` - Registration page
- `GET /user/login` - Login page
- `POST /user/create` - Create new user account
- `POST /user/authenticate` - Authenticate user
- `POST /user/logout` - Logout user

### Chat
- `GET /chat/show` - Chat interface
- `GET /chat/messages/{friend}` - Get conversation history
- `POST /chat/send` - Send message

### Messages
- `PATCH /chat/messages/seen` - Mark message as seen
- `PATCH /chat/messages/seen/bulk` - Mark entire chat as seen
- `PATCH /chat/messages/delivered` - Mark message as delivered
- `PUT /chat/messages/fallback` - Fallback message update (offline support)

### Contacts & Notifications
- `POST /contact/add` - Send friend request
- `GET /notifications` - Get user notifications
- `POST /notifications/{id}/accept` - Accept friend request
- `POST /notifications/{id}/reject` - Reject friend request
- `POST /notifications/read-all` - Mark all notifications as read

## 🔌 Broadcasting Events

### Private Channels
- `private-user.{user_id}` - User notifications and friend requests
- `private-chat.{user_id}` - Personal chat notifications

### Event Broadcasting
```javascript
// Listen to friend requests
Echo.private(`user.${userId}`)
    .listen('FriendRequestSent', (event) => {
        // Handle incoming friend request
    });

// Listen to messages
Echo.private(`chat.${userId}`)
    .listen('MessageSent', (event) => {
        // Handle incoming message
    });

// Real-time presence
Echo.join(`chat.${chatId}`)
    .here((users) => { })
    .joining((user) => { })
    .leaving((user) => { });
```

## 🧪 Testing

Run the test suite:
```bash
composer run test
```

Or directly:
```bash
php artisan test
```

## 📚 Key Concepts

### Event-Driven Architecture
The application heavily uses Laravel's event system:
1. Actions trigger events (e.g., `MessageSent` event)
2. Listeners respond to events (e.g., broadcast notifications)
3. Events are broadcast via WebSockets for real-time updates

### Message Status Flow
```
Message Created → MessageSent Event
    ↓
MessageDelivered Event (recipient online)
    ↓
MessageSeen Event (recipient opens chat)
```

### Real-Time Synchronization
- Reverb WebSocket server maintains persistent connections
- Events trigger broadcasts to subscribed channels
- Vite hot reload for development efficiency

## 🔐 Security Features

- **Laravel Authentication**: Secure session management
- **Middleware Protection**: `auth` middleware for protected routes
- **Channel Authorization**: Broadcasting channels require authentication
- **Input Validation**: Server-side request validation
- **CSRF Protection**: Laravel's built-in CSRF tokens

## 📝 Environment Variables

Key configuration options in `.env`:

```env
# Database
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Mail (for notifications)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com

# Reverb WebSocket
REVERB_APP_ID=12345
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

## 🐛 Troubleshooting

### WebSocket Connection Issues
```bash
# Restart Reverb
php artisan reverb:restart

# Check Reverb logs
php artisan pail
```

### Database Issues
```bash
# Refresh database
php artisan migrate:refresh --seed

# Check migration status
php artisan migrate:status
```

### Asset Build Issues
```bash
# Clear Vite cache
rm -rf node_modules/.vite

# Rebuild assets
npm run build
```

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Author

Created by [@adawwii](https://github.com/adawwii)

---

**Built with ❤️ using Laravel, Reverb, and modern PHP/JavaScript**
