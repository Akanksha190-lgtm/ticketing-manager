# Mann Travel Ticketing Manager - Authentication Setup Guide

## ✅ Authentication System Implemented

Your Laravel application now has a complete authentication system with a beautiful login interface.

---

## 🔑 Test Credentials

Use these credentials to log in immediately:

```
Email:    test@manntravel.com
Password: password123

OR

Email:    demo@manntravel.com
Password: demo1234
```

---

## 📁 Files Created/Modified

### Controllers
- **`app/Http/Controllers/AuthController.php`** - Main authentication controller with:
  - `showLogin()` - Display login form
  - `login()` - Process login credentials
  - `logout()` - Handle user logout
  - `showRegister()` - Display registration form
  - `register()` - Process user registration

### Views (Blade Templates)
- **`resources/views/auth/login.blade.php`** - Beautiful login page with your provided design
- **`resources/views/auth/register.blade.php`** - User registration form
- **`resources/views/dashboard.blade.php`** - Post-login dashboard

### Routes
- **`routes/web.php`** - Updated with authentication routes:
  - `GET /login` - Show login form
  - `POST /login` - Process login
  - `POST /logout` - Logout (protected)
  - `GET /register` - Show registration form
  - `POST /register` - Process registration
  - `GET /dashboard` - User dashboard (protected)
  - All ticketing routes protected with `auth` middleware

### Database
- **`database/seeders/UserSeeder.php`** - Seeder for test users
- Migrations already run to create users table

---

## 🚀 How to Use

### 1. Access the Application
```
http://localhost:8000/login
```

### 2. Login Flow
1. Navigate to `/login`
2. Enter email and password
3. Optional: Check "Keep me signed in" to persist session
4. Submit to authenticate
5. Redirected to `/dashboard`

### 3. Registration (New Users)
1. Go to `/login` and click "Request desk access"
2. Enter agency name, email, and password
3. Password must be at least 8 characters
4. System automatically logs in after successful registration

### 4. Logout
- Click "Sign out" button in the dashboard navbar
- Session is destroyed and user is redirected to login

---

## 🔒 Security Features

✅ **Session-based Authentication** - Uses Laravel's built-in session driver  
✅ **Password Hashing** - Passwords stored with bcrypt hashing  
✅ **CSRF Protection** - All forms protected with CSRF tokens  
✅ **Middleware Protection** - Ticketing routes require authentication  
✅ **Guest Middleware** - Login/register pages redirect if already logged in  
✅ **Session Regeneration** - Session ID regenerated after login  
✅ **Remember Me** - Optional persistent login functionality  

---

## 📝 Validation Rules

### Login
- Email: Required, valid email format
- Password: Required, string

### Registration
- Name: Required, max 255 characters
- Email: Required, valid email, unique in database
- Password: Required, min 8 characters, must be confirmed

---

## 🎨 Design Features

- Responsive design (works on mobile and desktop)
- Beautiful gradient backgrounds
- Smooth transitions and hover effects
- Error message display for validation failures
- Success alerts
- Password visibility toggle
- Remember me checkbox

---

## 📊 Database Schema

### users table (already created)
```
- id (Primary Key)
- name (string)
- email (unique)
- email_verified_at (nullable timestamp)
- password (hashed)
- remember_token (nullable)
- created_at
- updated_at
```

---

## 🔧 Configuration

### Authentication Guard (config/auth.php)
Default guard: `web` (session-based)

### Protected Routes
Add `->middleware('auth')` to routes that require login:
```php
Route::middleware('auth')->group(function () {
    Route::get('/ticketing', 'FareController@index');
    // ... more protected routes
});
```

### Public Routes
These routes are accessible without authentication:
```
GET  /login
POST /login
GET  /register
POST /register
GET  /
```

---

## 🚀 Quick Start Commands

### Start Development Server
```bash
php artisan serve
```
Then visit: `http://localhost:8000`

### Run Migrations (Already Done)
```bash
php artisan migrate
```

### Seed Test Users (Already Done)
```bash
php artisan db:seed --class=UserSeeder
```

### Clear Application Cache
```bash
php artisan cache:clear
php artisan config:clear
```

---

## 📱 Responsive Breakpoints

- **Desktop (>860px)**: Two-column layout with brand panel
- **Tablet/Mobile (<860px)**: Single column, brand panel hidden

---

## 🔄 User Flow

```
User Visits /
    ↓
If NOT Logged In → Redirect to /login
If Logged In → Redirect to /dashboard
    ↓
User Enters Credentials
    ↓
POST /login
    ↓
Credentials Valid? 
    ├─ YES → Session Created → Redirect to /dashboard
    └─ NO → Show Error → Redirect back to /login
    ↓
User Accesses /ticketing
    ↓
Auth Middleware Checks Session
    ├─ Valid → Show Ticketing Page
    └─ Invalid → Redirect to /login
    ↓
User Clicks Sign Out
    ├─ POST /logout
    └─ Session Destroyed → Redirect to /login
```

---

## 🎯 Next Steps

1. **Customize Login** - Update brand name, logo, or colors in CSS
2. **Add Email Verification** - Implement email verification for new registrations
3. **Password Reset** - Add "Forgot Password" functionality
4. **Two-Factor Auth** - Add 2FA for additional security
5. **OAuth** - Add social login (Google, GitHub, etc.)
6. **Audit Logging** - Track login/logout events for compliance

---

## ❓ Troubleshooting

### "Email already exists" error
- The email is already registered
- Use a different email or reset the database: `php artisan migrate:fresh --seed`

### "Session data not persisting"
- Check `.env` file has `SESSION_DRIVER=file`
- Clear sessions: `rm -r storage/framework/sessions/*`

### "Middleware not applied"
- Verify routes are inside `Route::middleware('auth')->group()`
- Clear route cache: `php artisan route:clear`

### "Can't login to dashboard"
- Verify database connection in `.env`
- Check users table has data: `php artisan tinker` → `User::all()`

---

## 📞 Support

For Laravel documentation on authentication:
- https://laravel.com/docs/authentication
- https://laravel.com/docs/middleware
- https://laravel.com/docs/blade

---

**Happy ticketing! 🎫✈️**
