# Location Controller Implementation

## Overview

The LocationController has been successfully created to handle user location updates. The controller allows authenticated users to update their latitude and longitude coordinates in their profile.

## Files Created/Modified

### 1. LocationController
**File:** `app/Http/Controllers/Api/V1/LocationController.php`

**Features:**
- Validates location data (latitude, longitude, and optional fields)
- Updates only latitude and longitude in the user's profile
- Comprehensive error handling and logging
- OpenAPI documentation for API documentation
- Proper authentication middleware

### 2. Route Configuration
**File:** `routes/api.php` (already existed)
- Route: `POST /api/v1/location/update`
- Middleware: `auth:sanctum`
- Controller: `LocationController@updateLocation`

### 3. Test Suite
**File:** `tests/Feature/LocationControllerTest.php`

**Test Cases:**
- User can update location successfully
- Authentication is required
- Required fields validation
- Latitude/longitude range validation
- Error handling when profile not found
- Optional fields acceptance

## API Endpoint

### POST /api/v1/location/update

**Request Body:**
```json
{
  "latitude": 37.7749,
  "longitude": -122.4194,
  "accuracy": 10,
  "altitude": 100,
  "heading": 90,
  "speed": 0
}
```

**Required Fields:**
- `latitude` (number, between -90 and 90)
- `longitude` (number, between -180 and 180)

**Optional Fields:**
- `accuracy` (number, minimum 0)
- `altitude` (number)
- `heading` (number, between 0 and 360)
- `speed` (number, minimum 0)

**Response (Success - 200):**
```json
{
  "status": "success",
  "message": "Location updated successfully",
  "data": {
    "latitude": 37.7749,
    "longitude": -122.4194,
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Response (Error - 400/404/500):**
```json
{
  "status": "error",
  "message": "Error message",
  "data": {}
}
```

## Database Schema

The location data is stored in the `profiles` table:
- `latitude` (decimal(10,7)) - User's latitude coordinate
- `longitude` (decimal(10,7)) - User's longitude coordinate

## Validation Rules

1. **Latitude**: Required, numeric, between -90 and 90 degrees
2. **Longitude**: Required, numeric, between -180 and 180 degrees
3. **Accuracy**: Optional, numeric, minimum 0
4. **Altitude**: Optional, numeric
5. **Heading**: Optional, numeric, between 0 and 360 degrees
6. **Speed**: Optional, numeric, minimum 0

## Security Features

- Authentication required via Laravel Sanctum
- Input validation and sanitization
- Error logging for debugging and monitoring
- Profile existence validation

## Usage Example

```php
// Using Laravel HTTP Client
$response = Http::withToken($token)
    ->post('/api/v1/location/update', [
        'latitude' => 37.7749,
        'longitude' => -122.4194,
        'accuracy' => 10
    ]);

if ($response->successful()) {
    $data = $response->json();
    // Handle success
}
```

## Testing

The implementation includes comprehensive tests covering:
- Successful location updates
- Authentication requirements
- Input validation
- Error scenarios
- Optional field handling

Run tests with:
```bash
php artisan test tests/Feature/LocationControllerTest.php
```

## Notes

- Only latitude and longitude are updated in the database
- Additional location data (accuracy, altitude, heading, speed) is logged for analytics
- The controller follows Laravel best practices and conventions
- OpenAPI documentation is included for automatic API documentation generation 