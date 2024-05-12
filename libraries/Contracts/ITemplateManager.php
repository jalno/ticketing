<?php

namespace packages\ticketing\Contracts;

interface ITemplateManager
{
    public function getByID(int $id): ITemplate;

    public function store(array $data): ITemplate;

    public function update(int $id, array $data): ITemplate;

    public function delete(int $id): ITemplate;
}
