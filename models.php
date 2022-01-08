<?php
class Worker{
    public string $name;
    public string $surname;
    public string $member_since;
    public int $id;
    public bool $is_admin;
    public string $password_hash;
    public function GetFullName(): string{
        return $this->name." ".$this->surname;
    }
}