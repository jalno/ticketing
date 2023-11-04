<?php

namespace packages\ticketing;

use packages\base\DB\DBObject;
use packages\ticketing\contracts\ILabel;
use packages\ticketing\contracts\ILabelManager;

/**
 * @phpstan-import-type StoreLabelDataType from ILabelManager
 * @phpstan-import-type UpdateLabelDataType from ILabelManager
 *
 * @property int         $id
 * @property string      $title
 * @property string      $color
 * @property string|null $description
 * @property int         $status
 */
class Label extends DBObject implements ILabel
{
    public const ACTIVE = 1;
    public const DEACTIVE = 2;

    /**
     * @var string
     */
    protected $dbTable = 'ticketing_labels';
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * @param array<string,array<string,string|bool>>
     */
    protected $dbFields = [
        'title' => ['type' => 'text', 'required' => true, 'unique' => true],
        'color' => ['type' => 'text', 'required' => true],
        'description' => ['type' => 'text'],
        'status' => ['type' => 'int', 'required' => true],
    ];

    public function getID(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array{old:UpdateLabelDataType,new:UpdateLabelDataType}
     */
    public function getChanges(ILabel $origin): array
    {
        $changes = ['new' => [], 'old' => []];
        foreach (['title', 'color', 'status', 'description'] as $item) {
            if ($origin->$item !== $this->$item) {
                $changes['old'][$item] = $origin->$item;
                $changes['new'][$item] = $this->$item;
            }
        }

        return $changes;
    }

    /**
     * @param StoreLabelDataType $data
     *
     * @return StoreLabelDataType
     */
    protected function preLoad(array $data): array
    {
        if (!isset($data['status'])) {
            $data['status'] = self::ACTIVE;
        }

        return $data;
    }
}
