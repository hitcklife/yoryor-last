<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return User::class;
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findByField('email', $email);
    }

    /**
     * Find user by phone
     *
     * @param string $phone
     * @return User|null
     */
    public function findByPhone(string $phone): ?User
    {
        return $this->findByField('phone', $phone);
    }

    /**
     * Find user with profile
     *
     * @param int $id
     * @return User|null
     */
    public function findWithProfile(int $id): ?User
    {
        return $this->findWithRelations($id, ['profile']);
    }

    /**
     * Find user with preferences
     *
     * @param int $id
     * @return User|null
     */
    public function findWithPreference(int $id): ?User
    {
        return $this->findWithRelations($id, ['preference']);
    }

    /**
     * Find user with profile and preferences
     *
     * @param int $id
     * @return User|null
     */
    public function findWithProfileAndPreference(int $id): ?User
    {
        return $this->findWithRelations($id, ['profile', 'preference']);
    }

    /**
     * Find users by gender
     *
     * @param string $gender
     * @return Collection
     */
    public function findByGender(string $gender): Collection
    {
        return $this->model->whereHas('profile', function ($query) use ($gender) {
            $query->where('gender', $gender);
        })->get();
    }

    /**
     * Find users by age range
     *
     * @param int $minAge
     * @param int $maxAge
     * @return Collection
     */
    public function findByAgeRange(int $minAge, int $maxAge): Collection
    {
        $minDate = now()->subYears($maxAge)->format('Y-m-d');
        $maxDate = now()->subYears($minAge)->format('Y-m-d');

        return $this->model->whereHas('profile', function ($query) use ($minDate, $maxDate) {
            $query->whereBetween('date_of_birth', [$minDate, $maxDate]);
        })->get();
    }

    /**
     * Find users by location within radius
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radius in kilometers
     * @return Collection
     */
    public function findByLocation(float $latitude, float $longitude, int $radius): Collection
    {
        // Haversine formula to calculate distance
        $haversine = "(
            6371 * acos(
                cos(radians($latitude))
                * cos(radians(profiles.latitude))
                * cos(radians(profiles.longitude) - radians($longitude))
                + sin(radians($latitude))
                * sin(radians(profiles.latitude))
            )
        )";

        return $this->model->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->selectRaw("users.*, $haversine AS distance")
            ->whereRaw("$haversine < ?", [$radius])
            ->orderBy('distance')
            ->get();
    }
}
