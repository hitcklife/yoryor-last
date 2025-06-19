<?php

namespace App\Swagger;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="YorYor API",
 *         description="API documentation for YorYor dating application",
 *         @OA\Contact(
 *             email="support@yoryor.com",
 *             name="YorYor Support"
 *         ),
 *         @OA\License(
 *             name="MIT",
 *             url="https://opensource.org/licenses/MIT"
 *         )
 *     )
 * )
 */


/**
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 */

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API endpoints for user authentication"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="API endpoints for user management"
 * )
 *
 * @OA\Tag(
 *     name="Profiles",
 *     description="API endpoints for user profiles"
 * )
 *
 * @OA\Tag(
 *     name="Matches",
 *     description="API endpoints for user matches"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Error",
 *     required={"status", "message"},
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="error"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Error message"
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         example={"field": {"Error message"}}
 *     )
 * )
 */
