#!/usr/bin/env bash
# ============================================================
# Mann Travel Authentication Setup Verification Script
# ============================================================
# Run this to verify your authentication setup

echo "🔍 Mann Travel Authentication System - Verification"
echo "=================================================="
echo ""

# Check if Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Not a Laravel project (artisan file missing)"
    exit 1
fi
echo "✅ Laravel project detected"

# Check if AuthController exists
if [ -f "app/Http/Controllers/AuthController.php" ]; then
    echo "✅ AuthController created"
else
    echo "❌ AuthController missing"
fi

# Check login view
if [ -f "resources/views/auth/login.blade.php" ]; then
    echo "✅ Login view created"
else
    echo "❌ Login view missing"
fi

# Check register view
if [ -f "resources/views/auth/register.blade.php" ]; then
    echo "✅ Registration view created"
else
    echo "❌ Registration view missing"
fi

# Check dashboard view
if [ -f "resources/views/dashboard.blade.php" ]; then
    echo "✅ Dashboard view created"
else
    echo "❌ Dashboard view missing"
fi

# Check UserSeeder
if [ -f "database/seeders/UserSeeder.php" ]; then
    echo "✅ UserSeeder created"
else
    echo "❌ UserSeeder missing"
fi

# Check users table
echo ""
echo "🗄️  Database Status:"
php artisan tinker <<EOF
try {
    $count = DB::table('users')->count();
    echo "✅ Users table exists with $count user(s)\n";
} catch (Exception $e) {
    echo "❌ Users table error: " . $e->getMessage() . "\n";
}
exit;
EOF

echo ""
echo "📋 Routes Available:"
php artisan route:list --path=login --path=register --path=logout --path=dashboard 2>/dev/null || echo "Use: php artisan route:list to see all routes"

echo ""
echo "=================================================="
echo "✅ Setup Verification Complete!"
echo ""
echo "Next steps:"
echo "1. Start server: php artisan serve"
echo "2. Visit: http://localhost:8000/login"
echo "3. Use test credentials:"
echo "   - Email: test@manntravel.com"
echo "   - Password: password123"
echo ""
echo "For more info, see AUTH_QUICK_REFERENCE.md"
