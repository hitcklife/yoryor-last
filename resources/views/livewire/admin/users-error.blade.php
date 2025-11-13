<div class="bg-red-50 border border-red-200 rounded-lg p-6">
    <div class="flex items-center mb-4">
        <svg class="w-6 h-6 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <h3 class="text-lg font-medium text-red-800">User Management Error</h3>
    </div>
    <div class="text-red-700 mb-4">
        <p><strong>Error:</strong> {{ $error }}</p>
    </div>
    <div class="text-red-600">
        <p>This might be due to missing database tables or relationships. Please check:</p>
        <ul class="list-disc list-inside mt-2 space-y-1">
            <li>Run migrations: <code class="bg-red-100 px-2 py-1 rounded">php artisan migrate</code></li>
            <li>Seed roles: <code class="bg-red-100 px-2 py-1 rounded">php artisan db:seed --class=RoleSeeder</code></li>
            <li>Check if Profile model relationships are correctly set up</li>
        </ul>
    </div>
</div>