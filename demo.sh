#!/bin/bash

set -e

CURRENT_PACKAGE_PATH=$(pwd)

# Detect Valet Sites directory or use current directory
if [ -d "$HOME/.config/valet/Sites" ]; then
    DEMO_BASE_DIR="$HOME/.config/valet/Sites"
    USING_VALET=true
    echo "🌐 Detected Laravel Valet - will use Sites directory"
else
    DEMO_BASE_DIR="$CURRENT_PACKAGE_PATH"
    USING_VALET=false
fi

DEMO_DIR="$DEMO_BASE_DIR/trees-for-agents-test"

echo "🌳 Trees for Agents - Demo Setup"
echo "================================"
echo ""

# Check if Laravel CLI is installed
if ! command -v laravel &> /dev/null; then
    echo "📦 Installing Laravel CLI..."
    composer global require laravel/installer
    echo ""
fi

# Check if demo directory already exists
if [ -d "$DEMO_DIR" ]; then
    echo "⚠️  Demo directory '$DEMO_DIR' already exists."
    read -p "Delete it and start fresh? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        rm -rf "$DEMO_DIR"
    else
        echo "❌ Cancelled"
        exit 1
    fi
fi

# Create Laravel app
echo "🚀 Creating fresh Laravel app..."
laravel new "$DEMO_DIR" --quiet
cd "$DEMO_DIR"

# Add the local package as a repository
echo "📦 Configuring local package..."
composer config repositories.local path "$CURRENT_PACKAGE_PATH"

# Install dependencies
echo "📦 Installing Trees for Agents and Livewire..."
composer require host-uk/trees-for-agents:@dev livewire/livewire --quiet

# Create demo route
echo "🛣️  Creating demo route..."
cat > routes/web.php << 'EOF'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('trees');
});

Route::get('/trees', function () {
    return view('trees');
})->name('trees');
EOF

# Create trees view
echo "🎨 Creating trees view..."
cat > resources/views/trees.blade.php << 'EOF'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trees for Agents - Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-900">
    <livewire:trees-leaderboard
        site-name="Demo Site"
        referral-base="https://demo.example.com/ref"
    />

    @livewireScripts
</body>
</html>
EOF

# Enable API routes for Laravel 12
echo "🔌 Enabling API routes..."
sed -i '' 's/web: __DIR__.*routes\/web.php\x27,/web: __DIR__.\x27\.\.\/routes\/web.php\x27,\n        api: __DIR__.\x27\.\.\/routes\/api.php\x27,/' bootstrap/app.php

# Create API routes file
cat > routes/api.php << 'EOF'
<?php

use Illuminate\Support\Facades\Route;

// API routes are registered here
// The Trees for Agents package routes are auto-registered via service provider
EOF

echo ""
echo "✅ Demo setup complete!"
echo ""

if [ "$USING_VALET" = true ]; then
    echo "🌐 Demo is ready with Laravel Valet!"
    echo "   Visit: https://trees-for-agents-test.test"
    echo "   Trees: https://trees-for-agents-test.test/trees"
    echo "   Health: https://trees-for-agents-test.test/api/trees/health"
else
    echo "🚀 Start the development server with:"
    echo "   cd $DEMO_DIR && php artisan serve"
    echo ""
    echo "Then visit:"
    echo "   Homepage: http://localhost:8000"
    echo "   Trees: http://localhost:8000/trees"
    echo "   Health: http://localhost:8000/api/trees/health"
fi
echo ""
