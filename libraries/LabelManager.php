<?php

namespace packages\ticketing;

use packages\base\View\Error;
use packages\ticketing\Contracts\ILabel;
use packages\ticketing\Contracts\ILabelManager;
use packages\ticketing\Contracts\IServiceProvider;

/**
 * @phpstan-import-type StoreLabelDataType from ILabelManager
 * @phpstan-import-type UpdateLabelDataType from ILabelManager
 */
class LabelManager implements ILabelManager
{
    public IServiceProvider $serviceProvider;

    public function __construct(IServiceProvider $serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
    }

    /**
     * @throws Error on failure
     */
    public function getByID(int $id): ILabel
    {
        $label = (new Label())->byId($id);

        if (!$label) {
            throw new Error('label.not_found');
        }

        return $label;
    }

    /**
     * @param StoreLabelDataType $data
     *
     * @throws \InvalidArgumentException
     * @throws Error                     on store failure
     */
    public function store(array $data): ILabel
    {
        foreach (['title', 'color'] as $requiredItem) {
            if (!isset($data[$requiredItem])) {
                throw new \InvalidArgumentException($requiredItem);
            }
        }

        $label = new Label($data);
        $result = $label->save();

        if (!$result) {
            throw new Error('label.store');
        }

        return $label;
    }

    /**
     * @throws Error on failure update or find label
     */
    public function update(int $id, array $changes): ILabel
    {
        $label = $this->getByID($id);

        foreach (['title', 'color', 'status'] as $item) {
            if (isset($changes[$item]) and $label->$item !== $changes[$item]) {
                $label->$item = $changes[$item];
            }
        }

        if (array_key_exists('description', $changes)) {
            if ($label->description !== $changes['description']) {
                $label->description = $changes['description'];
            }
        }

        $label->save();

        return $label;
    }

    /**
     * @throws Error on failure delete or find label
     */
    public function destroy(int $id): ILabel
    {
        $label = $this->getByID($id);

        $data = $label->toArray();
        $result = $label->delete();

        if (!$result) {
            throw new Error('label.destroy');
        }

        return $label;
    }
}
