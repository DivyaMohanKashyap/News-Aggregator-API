<?php
namespace App\DTO;

use Illuminate\Support\Facades\Hash;

class UserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public $password
    ) {}
}
