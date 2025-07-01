#!/bin/bash

echo "🚀 Starting Chat System Optimization Setup..."

# Check if PHP and Artisan are available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed or not in PATH"
    exit 1
fi

if [ ! -f "artisan" ]; then
    echo "❌ Laravel artisan file not found. Make sure you're in the Laravel project root."
    exit 1
fi

echo "✅ PHP and Laravel detected"

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate

# Clear caches
echo "🧹 Clearing application caches..."
php artisan cache:clear
php artisan config:clear

# Generate API documentation (if L5-Swagger is installed)
if php artisan list | grep -q "l5-swagger:generate"; then
    echo "📚 Generating API documentation..."
    php artisan l5-swagger:generate
else
    echo "⚠️  L5-Swagger not found. Skipping API documentation generation."
fi

# Check if the optimization migration exists
if [ -f "database/migrations/2025_01_03_000000_optimize_messages_table.php" ]; then
    echo "✅ Chat optimization migration found"
else
    echo "⚠️  Chat optimization migration not found"
fi

# Verify new models exist
if [ -f "app/Models/MessageRead.php" ]; then
    echo "✅ MessageRead model created"
else
    echo "❌ MessageRead model not found"
fi

echo ""
echo "🎉 Chat System Optimization Setup Complete!"
echo ""
echo "📋 Summary of Changes:"
echo "  • Created MessageRead model for efficient read tracking"
echo "  • Enhanced ChatUser model with helper methods"
echo "  • Optimized Message model with new scopes and methods"
echo "  • Updated ChatController with performance improvements"
echo "  • Added new API endpoints for better mobile integration"
echo "  • Created comprehensive API documentation"
echo ""
echo "📖 Next Steps:"
echo "  • Review docs/chat-api-documentation.md for mobile integration"
echo "  • Review docs/chat-optimization-summary.md for technical details"
echo "  • Test the new API endpoints with your mobile app"
echo "  • Consider implementing WebSocket integration for real-time features"
echo ""
echo "🔗 New API Endpoints:"
echo "  • POST /api/v1/chats/create - Create or get existing chat"
echo "  • GET /api/v1/chats/unread-count - Get total unread counts"
echo "  • DELETE /api/v1/chats/{id} - Delete/leave chat"
echo ""
echo "Happy coding! 🚀"