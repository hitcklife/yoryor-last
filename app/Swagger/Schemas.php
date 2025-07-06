<?php

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

/**
 * @OA\Schema(
 *   schema="UserResource",
 *   type="object",
 *   @OA\Property(property="type", type="string", example="users"),
 *   @OA\Property(property="id", type="string", example="1"),
 *   @OA\Property(
 *     property="attributes",
 *     type="object",
 *     @OA\Property(property="email", type="string", example="user@example.com"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="profile_photo_path", type="string", example="/storage/photos/1.jpg"),
 *     @OA\Property(property="registration_completed", type="boolean", example=true),
 *     @OA\Property(property="is_private", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="age", type="integer", example=25, nullable=true),
 *     @OA\Property(property="full_name", type="string", example="John Doe", nullable=true),
 *     @OA\Property(property="is_online", type="boolean", example=true),
 *     @OA\Property(property="last_active_at", type="string", format="date-time", nullable=true)
 *   ),
 *   @OA\Property(property="included", type="array", @OA\Items(type="object"), nullable=true)
 * )
 */

/**
 * @OA\Schema(
 *   schema="StoryResource",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="user_id", type="integer", example=1),
 *   @OA\Property(property="media_url", type="string", example="https://example.com/story.jpg"),
 *   @OA\Property(property="thumbnail_url", type="string", example="https://example.com/story-thumb.jpg"),
 *   @OA\Property(property="type", type="string", example="image"),
 *   @OA\Property(property="caption", type="string", example="My story caption"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="expires_at", type="string", format="date-time"),
 *   @OA\Property(property="status", type="string", example="active"),
 *   @OA\Property(property="is_expired", type="boolean", example=false),
 *   @OA\Property(property="user", ref="#/components/schemas/UserResource", nullable=true)
 * )
 */ 