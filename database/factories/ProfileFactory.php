<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);
        
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName($gender),
            'last_name' => fake()->lastName(),
            'date_of_birth' => fake()->date('-30 years', '-18 years'),
            'gender' => $gender,
            'bio' => fake()->paragraph(3),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'occupation' => fake()->jobTitle(),
            'education' => fake()->randomElement(['high_school', 'bachelor', 'master', 'phd']),
            'height' => fake()->numberBetween(150, 200),
            'religion' => 'Islam',
            'drinking' => fake()->randomElement(['never', 'socially', 'regularly']),
            'smoking' => fake()->randomElement(['never', 'socially', 'regularly']),
            'languages' => ['Uzbek', 'English'],
            'interests' => fake()->randomElements(
                ['Travel', 'Photography', 'Cooking', 'Reading', 'Sports', 'Music', 'Movies', 'Technology'],
                fake()->numberBetween(3, 5)
            ),
        ];
    }
}