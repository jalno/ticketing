<?php

namespace packages\ticketing\contracts;

use packages\ticketing\Department;

/**
 * @phpstan-type IDataType array{title?:string,subject?:string,department_id?:string,content?:string,message_type?:int,message_format?:string,variables?:string[],status?:int}
 * @phpstan-type IChangesType array{new:IDataType,old:IDataType}
 */
interface ITemplate
{
    public function getID(): int;

    public function getTitle(): string;

    public function getSubject(): ?string;

    public function getDepartmentID(): ?int;

    public function getDepartment(): ?Department;

    public function getContent(): string;

    public function getMessageType(): ?int;

    public function getMessageFormat(): string;

    public function getStatus(): int;

    /**
     * @return IChangesType
     */
    public function getChanges(ITemplate $origin): array;
}
