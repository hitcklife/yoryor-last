<?php

namespace App\Repositories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Collection;

class ProfileRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return Profile::class;
    }

    /**
     * Find profile by user ID
     *
     * @param int $userId
     * @return Profile|null
     */
    public function findByUserId(int $userId): ?Profile
    {
        return $this->findByField('user_id', $userId);
    }

    /**
     * Find profiles by gender
     *
     * @param string $gender
     * @return Collection
     */
    public function findByGender(string $gender): Collection
    {
        return $this->findWhere(['gender' => $gender]);
    }

    /**
     * Find profiles by country
     *
     * @param int $countryId
     * @return Collection
     */
    public function findByCountry(int $countryId): Collection
    {
        return $this->findWhere(['country_id' => $countryId]);
    }

    /**
     * Find profiles by city
     *
     * @param string $city
     * @return Collection
     */
    public function findByCity(string $city): Collection
    {
        return $this->findWhere(['city' => $city]);
    }

    /**
     * Find profiles by age range
     *
     * @param int $minAge
     * @param int $maxAge
     * @return Collection
     */
    public function findByAgeRange(int $minAge, int $maxAge): Collection
    {
        $minDate = now()->subYears($maxAge)->format('Y-m-d');
        $maxDate = now()->subYears($minAge)->format('Y-m-d');

        return $this->model->whereBetween('date_of_birth', [$minDate, $maxDate])->get();
    }

    /**
     * Find profiles by location within radius
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
                * cos(radians(latitude))
                * cos(radians(longitude) - radians($longitude))
                + sin(radians($latitude))
                * sin(radians(latitude))
            )
        )";

        return $this->model->selectRaw("*, $haversine AS distance")
            ->whereRaw("$haversine < ?", [$radius])
            ->orderBy('distance')
            ->get();
    }

    /**
     * Find profiles with user
     *
     * @return Collection
     */
    public function findWithUser(): Collection
    {
        return $this->model->with('user')->get();
    }

    /**
     * Find profiles with country
     *
     * @return Collection
     */
    public function findWithCountry(): Collection
    {
        return $this->model->with('country')->get();
    }

    /**
     * Find profiles with user and country
     *
     * @return Collection
     */
    public function findWithUserAndCountry(): Collection
    {
        return $this->model->with(['user', 'country'])->get();
    }
}
