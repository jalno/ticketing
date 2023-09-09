<?php

namespace packages\ticketing;

use packages\base\DB\DBObject;
use packages\ticketing\contracts\ITemplate;
use packages\ticketing\ticket_message as Message;

/**
 * @phpstan-import-type IChangesType from ITemplate
 * @phpstan-type DataType array{id?:int,title?:string,department_id?:int,content?:string,message_type?:Template::ADD|Template::REPLY|null,message_format?:Message::html|Message::markdown,status?:Template::ACTIVE|Template::REPLY}
 *
 * @property int         $id
 * @property string|null $subject
 * @property int|null    $department_id
 * @property string      $content
 * @property int|null    $message_type
 * @property string|null $message_format
 * @property int         $status
 * @property Department  $department
 */
class Template extends DBObject implements ITemplate
{
    public const ADD = 1;
    public const REPLY = 2;

    public const ACTIVE = 1;
    public const DEACTIVE = 2;

    public static function extractVariables(string $content): array
    {
        if (!preg_match_all('/{{[^}]+}}/', $content, $matches)) {
            return [];
        }

        $predefinedVariables = ['{{user_name}}', '{{user_lastname}}', '{{user_full_name}}', '{{user_email}}', '{{user_cellphone}}'];

        return array_diff($matches[0], $predefinedVariables);
    }

    /**
     * @var string
     */
    protected $dbTable = 'ticketing_templates';
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * @param array<string,array<string,string|bool>>
     */
    protected $dbFields = [
        'title' => ['type' => 'text', 'required' => true, 'unique' => true],
        'subject' => ['type' => 'text'],
        'department_id' => ['type' => 'int'],
        'content' => ['type' => 'text', 'required' => true],
        'message_type' => ['type' => 'int'],
        'message_format' => ['type' => 'text', 'required' => true],
        'status' => ['type' => 'int', 'required' => true],
    ];

    /**
     * @var array<string,string[]>
     */
    protected $relations = [
        'department' => ['hasOne', Department::class, 'department_id'],
    ];

    public function getID(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getDepartmentID(): ?int
    {
        return $this->department_id;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMessageType(): ?int
    {
        return $this->message_type;
    }

    public function getMessageFormat(): string
    {
        return $this->message_format;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return IChangesType
     */
    public function getChanges(ITemplate $origin): array
    {
        $changes = ['new' => [], 'old' => []];
        foreach (['title', 'subject', 'department_id', 'content', 'message_type', 'message_format', 'status'] as $item) {
            if ($this->{$item} != $origin->{$item}) {
                $changes['new'][$item] = $this->{$item};
                $changes['old'][$item] = $origin->{$item};
            }
        }

        return $changes;
    }

    /**
     * @param DataType $data
     *
     * @return DataType
     */
    protected function preLoad(array $data): array
    {
        if (!isset($data['message_format'])) {
            $data['message_format'] = Message::html;
        }

        return $data;
    }
}
