<?php

namespace themes\clipone\views\ticketing;

use packages\ticketing\Department;
use packages\ticketing\Template;

/**
 * @phpstan-import-type SelectOptionType from HelperTrait
 */
trait TemplateTrait
{
    /**
     * @return SelectOptionType[]
     */
    public function getMessageTypesForSelect(): array
    {
        return [
            [
                'title' => t('titles.ticketing.all'),
                'value' => '',
            ],
            [
                'title' => t('titles.ticketing.templates.message_type.add'),
                'value' => Template::ADD,
            ],
            [
                'title' => t('titles.ticketing.templates.message_type.reply'),
                'value' => Template::REPLY,
            ],
        ];
    }

    /**
     * @return SelectOptionType[]
     */
    public function getDepartmentsForSelect(): array
    {
        $options = [
            [
                'title' => t('titles.ticketing.all'),
                'value' => '',
            ],
        ];

        $query = new Department();
        $query->where('status', Department::ACTIVE);

        $departments = $query->get();

        foreach ($departments as $department) {
            $options[] = [
                'title' => $department->title,
                'value' => $department->id,
            ];
        }

        return $options;
    }

    public function loadTutorialModel(): void
    {
        require_once __DIR__.'/../../html/settings/templates/TutorialModal.php';
    }
}
