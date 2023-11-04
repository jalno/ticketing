<?php

namespace packages\ticketing\contracts;

interface ILabel
{
    public function getID(): int;

    public function getTitle(): string;

    public function getDescription(): ?string;

    public function getColor(): string;

    public function getStatus(): int;
}
