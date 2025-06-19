<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Get all records
     *
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*']);

    /**
     * Get paginated records
     *
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate(int $perPage = 15, array $columns = ['*']);

    /**
     * Create a new record
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update a record
     *
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id);

    /**
     * Delete a record
     *
     * @param int $id
     * @return mixed
     */
    public function delete(int $id);

    /**
     * Find a record by ID
     *
     * @param int $id
     * @param array $columns
     * @return mixed
     */
    public function find(int $id, array $columns = ['*']);

    /**
     * Find a record by field value
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return mixed
     */
    public function findByField(string $field, $value, array $columns = ['*']);

    /**
     * Find a record by multiple fields
     *
     * @param array $where
     * @param array $columns
     * @return mixed
     */
    public function findWhere(array $where, array $columns = ['*']);

    /**
     * Find a record with relationships
     *
     * @param int $id
     * @param array $relations
     * @return mixed
     */
    public function findWithRelations(int $id, array $relations = []);
}
