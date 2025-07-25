<?php
namespace App\DTO;

class UserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public $password
    ) {
        $this->setPassword($password);
    }

    public function setPassword(string $plaintextPassword): void {
        $this->password = password_hash($plaintextPassword, PASSWORD_DEFAULT);
    }
}
