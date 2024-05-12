<?php

namespace packages\ticketing;

use packages\base\DB\DBObject;

class TicketParam extends DBObject
{
    protected $dbTable = 'ticketing_tickets_params';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'ticket' => ['type' => 'int', 'required' => true],
        'name' => ['type' => 'text', 'required' => true],
        'value' => ['type' => 'text', 'required' => true],
    ];
    protected $relations = [
        'ticket' => ['hasOne', 'packages\\ticketing\\ticket', 'ticket'],
    ];
}
