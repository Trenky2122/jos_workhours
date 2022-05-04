<?php
class Worker{
    public string $name;
    public string $surname;
    public string $email;
    public string $member_since;
    public int $id;
    public bool $is_admin;
    public string $password_hash;
    public string $username;
    public string $clockify_api_key;
    public function GetFullName(): string{
        return $this->name." ".$this->surname;
    }
}

const PARTNERS_PROJECT_ID = 8;

class WorkerDayClockify{
    public string $begin_time;
    public string $end_time;
    public ?string $break_begin;
    public ?string $break_end;
    public string $work_day_date;
    public string $description;
    public int $worker_id;
    public array $projects;
}

class WorkDay{
    public string $id;
    public string $day;
    public string $week;
    public string $month;
    public string $day_of_week;
}

class WorkerWorkDay{
    public int $id;
    public int $worker_id;
    public int $work_day_id;
    public ?string $begin_time;
    public ?string $end_time;
    public ?string $break_begin;
    public ?string $break_end;
    public array $projects;
    public ?string $description;
    public bool $done;
}

class Project{
    public int $id;
    public string $name;
    public bool $active;
    public ?string $time;
}