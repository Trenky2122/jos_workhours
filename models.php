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

class Week{
    public string $db_id;
    public string $monday;

}

class WorkDay{
    public int $id;
    public string $day;
    public string $week;
    public string $month;
    public string $day_of_week;
}