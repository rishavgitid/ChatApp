<?php

namespace App\Interfaces;

interface UserRepositoryInterface
{
    public function allUsers($exceptId = null);
    public function find($id);
    public function findByEmail($email);
    public function create(array $data);
}
