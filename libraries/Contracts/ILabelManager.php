<?php

namespace packages\ticketing\Contracts;

use packages\base\View\Error;

/**
 * @phpstan-type StoreLabelDataType array{title: string, color: string, description?: string, status?: int}
 * @phpstan-type UpdateLabelDataType array{title?: string, color?: string, description?: string, status?: int}
 */
interface ILabelManager
{
    /**
     * @throws Error on failure
     */
    public function getByID(int $id): ILabel;

    /**
     * @param StoreLabelDataType $data
     *
     * @throws Error on store failure
     */
    public function store(array $data): ILabel;

    /**
     * @throws Error on failure update or find label
     */
    public function update(int $id, array $changes): ILabel;

    /**
     * @throws Error on failure delete or find label
     */
    public function destroy(int $id): ILabel;
}
