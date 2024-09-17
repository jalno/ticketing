<?php

namespace packages\ticketing\Department;

use packages\base\DB\DBObject;
use packages\ticketing\Department;

class WorkTime extends DBObject
{
    public const saturday = 6;
    public const sunday = 0;
    public const monday = 1;
    public const tuesday = 2;
    public const wednesday = 3;
    public const thursday = 4;
    public const friday = 5;

    public static function getDays(): array
    {
        return [self::saturday, self::sunday, self::monday, self::tuesday, self::wednesday, self::thursday, self::friday];
    }
    protected $dbTable = 'ticketing_departments_worktimes';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'department' => ['type' => 'int', 'required' => true],
        'day' => ['type' => 'int', 'required' => true],
        'time_start' => ['type' => 'int'],
        'time_end' => ['type' => 'int'],
        'message' => ['type' => 'text'],
    ];
    protected $relations = [
        'department' => ['hasOne', Department::class, 'department'],
    ];
}
