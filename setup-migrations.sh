#!/bin/bash

echo "🚀 Setting up consolidated migrations for YorYor Dating App..."

# Function to test migrations
test_migrations() {
    echo "📋 Testing migrations..."
    php artisan migrate:fresh --force
    if [ $? -eq 0 ]; then
        echo "✅ Migrations completed successfully!"
    else
        echo "❌ Migration failed. Please check the error above."
        exit 1
    fi
}

# Function to rollback for testing
rollback_migrations() {
    echo "🔄 Rolling back migrations for testing..."
    php artisan migrate:rollback --force
}

# Main execution
echo "📁 Migration folders created:"
ls -la database/migrations/*/

echo ""
echo "📝 Key migrations to implement:"
echo "1. Core tables (users, countries, profiles, etc.)"
echo "2. Profile extensions (cultural, family, career, etc.)"
echo "3. Chat system (chats, messages, calls)"
echo "4. Matching system (matches, likes, photos)"
echo "5. Settings (comprehensive user settings)"
echo "6. Subscription & Payment"
echo "7. Safety & Moderation"
echo "8. Matchmaker system"
echo "9. Auth & Roles"
echo "10. Foreign key constraints (run last)"

echo ""
echo "⚠️  IMPORTANT NOTES:"
echo "- Calls table must be created BEFORE messages table (foreign key dependency)"
echo "- Matchmakers table must be created BEFORE adding users.assigned_matchmaker_id FK"
echo "- Run foreign key constraints migration last"

echo ""
read -p "Do you want to test the migrations now? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    test_migrations
fi

echo ""
echo "📚 Documentation available at: docs/migrations/"
echo "✨ Setup complete!"