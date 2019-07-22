<?php


namespace API;


interface ControllerInterface
{
    public function printList(int $page, int $perPage): void;

    public function print(int $id): void;

    public function add(array $data): void;

    public function update(int $id, array $data): void;

    public function delete(int $id): void;
}