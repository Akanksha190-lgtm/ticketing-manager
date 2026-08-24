# 🎫 Mann Travel Authentication - Quick Reference

## ✅ What's Ready

### 🔐 Authentication System
- Login with email & password
- User registration with validation
- Session-based authentication
- "Remember me" functionality
- Secure password hashing (bcrypt)
- CSRF protection on all forms

### 🎨 User Interface
- Professional login page with your provided design
- Responsive layout (mobile-friendly)
- Beautiful registration form
- Error messages and validation feedback
- Dashboard with user welcome

### 🛡️ Security
- Middleware protecting ticketing routes
- Session regeneration after login
- Password validation (min 8 chars)
- Unique email enforcement
- Guest middleware on login/register

---

## 🚀 Get Started in 30 Seconds

### Step 1: Start Laravel Server
```bash
cd d:\laragon\www\ticketing-manager
php artisan serve
```

### Step 2: Open Login Page
```
http://localhost:8000/login
```

### Step 3: Login with Test Account
```
📧 Email:    test@manntravel.com
🔑 Password: password123
```

---

## 📍 Key Routes

| Route | Method | Public? | Purpose |
|-------|--------|---------|---------|
| `/login` | GET | ✅ | Show login form |
| `/login` | POST | ✅ | Process login |
| `/register` | GET | ✅ | Show register form |
| `/register` | POST | ✅ | Process registration |
| `/dashboard` | GET | 🔒 | User dashboard |
| `/logout` | POST | 🔒 | Sign out user |
| `/ticketing` | GET | 🔒 | Ticketing desk |

---

## 📂 File Structure

```
app/Http/Controllers/
  └── AuthController.php          ← Login/Register logic

resources/views/
  ├── auth/
  │   ├── login.blade.php         ← Login page (with your design)
  │   └── register.blade.php      ← Registration page
  └── dashboard.blade.php         ← Post-login dashboard

routes/
  └── web.php                     ← Updated with auth routes

database/seeders/
  └── UserSeeder.php              ← Test users
```

---

## 💡 Usage Examples

### In Blade Template - Check if Logged In
```blade
@auth
    <p>Welcome, {{ Auth::user()->name }}!</p>
@endauth

@guest
    <p>Please login first</p>
@endguest
```

### In Controller - Get Current User
```php
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
echo $user->email;
echo $user->name;
```

### Protect Routes
```php
// Already protected in routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/ticketing', 'FareController@index');
});
```

---

## 🧪 Test Accounts

| Email | Password | Agency |
|-------|----------|--------|
| test@manntravel.com | password123 | Test Agency |
| demo@manntravel.com | demo1234 | Demo User |

**To add more users:** Use registration form at `/register`

---

## 📊 Database Tables

✅ **users** table created with:
- id, name, email, password, remember_token
- email_verified_at, created_at, updated_at

---

## 🔄 User Journey

```
Visit localhost:8000
     ↓
Not logged in? → Redirect to /login
Logged in? → Redirect to /dashboard
     ↓
Enter credentials
     ↓
Valid? → Dashboard (with navigation)
Invalid? → Error message, try again
     ↓
Click "Sign out" → Logout & back to login
```

---

## 🎨 UI Features

- ✨ Gradient navy/blue theme from provided design
- 📱 Mobile-responsive layout
- 🎯 Clean, modern interface
- 👁️ Password visibility toggle
- ✔️ Remember me checkbox
- 📧 Email validation
- 🔐 Secure password field

---

## ⚙️ Configuration

**Environment:** `.env` (ensure these are set)
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=ticketing_manager
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
```

---

## 🆘 Common Issues & Fixes

### "The page doesn't load"
→ Run `php artisan serve` from project root

### "Login fails with correct credentials"
→ Check database connection in `.env`

### "Passwords don't match"
→ Password minimum 8 characters, confirm field must match

### "Email already taken"
→ Use a different email or check existing users with this command:
```bash
php artisan tinker
User::all()
```

---

## 🔐 Security Checklist

✅ Passwords hashed with bcrypt  
✅ CSRF tokens on all forms  
✅ Session regeneration on login  
✅ Middleware prevents unauthorized access  
✅ Email uniqueness enforced  
✅ Input validation on all fields  

---

## 📞 Documentation Files

- **AUTHENTICATION_SETUP.md** - Complete setup guide
- **README.md** - Project overview

---

**Ready to use! 🚀 All routes are live and tested.**

*Last updated: 2026-08-19*
