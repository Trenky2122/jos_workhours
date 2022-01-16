<?php
include "config.php";
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS,
    DB_NAME, DB_PORT);
$mysqli->query("create table if not exists projects
(
    id     int auto_increment
        primary key,
    name   varchar(25)          not null,
    active tinyint(1) default 1 not null,
    constraint projects_name_uindex
        unique (name)
);");
$mysqli->query("
create table if not exists workers
(
    id            int auto_increment
        primary key,
    name          varchar(30)                                            not null,
    surname       varchar(30)                                            not null,
    member_since  date                                                   null,
    password_hash varchar(32) default '1bd261529083ab372e5911b3c9e0cc71' not null,
    is_admin      tinyint(1)  default 0                                  not null
);");

$mysqli->query("

create table if not exists default_days
(
    work_day_number int  not null,
    id              int auto_increment
        primary key,
    worker_id       int  not null,
    begin_time      time null,
    end_time        time null,
    break_begin     time null,
    break_end       time null,
    description     text null,
    constraint default_days_work_day_number_worker_id_uindex
        unique (work_day_number, worker_id),
    constraint default_days_workers_id_fk
        foreign key (worker_id) references workers (id)
            on update cascade on delete cascade
);");
$mysqli->query("
create table if not exists workers_workday
(
    id            int auto_increment
        primary key,
    worker_id     int        not null,
    work_day_date date       not null,
    begin_time    time       not null,
    end_time      time       not null,
    break_begin   time       null,
    break_end     time       null,
    description   text       not null,
    done          tinyint(1) not null,
    constraint workers_workday_worker_id_work_day_date_uindex
        unique (worker_id, work_day_date),
    constraint workers_workday_workers_id_fk
        foreign key (worker_id) references workers (id)
);");
$mysqli->query("
create table if not exists workday_project
(
    id                int auto_increment
        primary key,
    worker_workday_id int  not null,
    project_id        int  not null,
    time              time not null,
    constraint workday_project_worker_workday_id_project_id_uindex
        unique (worker_workday_id, project_id),
    constraint workday_project_worker_workday_id_project_id_uindex_2
        unique (worker_workday_id, project_id),
    constraint workday_project_projects_id_fk
        foreign key (project_id) references projects (id),
    constraint workday_project_workers_workday_id_fk
        foreign key (worker_workday_id) references workers_workday (id)
            on update cascade on delete cascade
);");

include_once "service.php";
$service = new Service();
$service->CreateAdminWorker("Ján", "Osadský st.", "2008-06-27");
if($mysqli->errno){
    echo $mysqli->error;
}
else{
    echo "Pohode.";
    unlink("setup.php");
}

