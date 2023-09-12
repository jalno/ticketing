<?php

namespace themes\clipone\views\ticketing;

use packages\base\DB\Parenthesis;
use packages\ticketing\Template;
use packages\ticketing\ticket_message as Message;
use themes\clipone\Navigation;
use themes\clipone\navigation\MenuItem;

/**
 * @phpstan-type SelectOptionType array{title:string,value:string,int}
 */
trait HelperTrait
{
    /**
     *  @var \packages\ticketing\Department[]
     */
    public array $accessedDepartments = [];

    public static function getTicketingSettingsMenu(): MenuItem
    {
        $settingsMenu = Navigation::getByName('settings/settings_ticketing');
        if (!$settingsMenu) {
            $settingsMenu = new MenuItem('settings_ticketing');
            $settingsMenu->setTitle(t('titles.settings_ticketing'));
            $settingsMenu->setIcon('clip-user-6');

            Navigation::getByName('settings')->addItem($settingsMenu);
        }

        return $settingsMenu;
    }

    public function getTicketingSettingsMenuItemName(string $name)
    {
        return 'settings/settings_ticketing/'.$name;
    }

    public function loadContentEditor(): void
    {
        require_once __DIR__.'/../../html/ContentEditor.php';
    }

    /**
     * @return SelectOptionType[]
     */
    public function getMessageFormatsForSelect(bool $withPlaceholder = false): array
    {
        $options = [];

        if ($withPlaceholder) {
            $options[] = [
                'title' => t('choose'),
                'value' => '',
            ];
        }

        $options[] = [
            'title' => t('titile.ticketing.message_format.html'),
            'value' => Message::html,
        ];

        $options[] = [
            'title' => t('titile.ticketing.message_format.markdown'),
            'value' => Message::markdown,
        ];

        return $options;
    }

    /**
     * @return SelectOptionType[]
     */
    public function getTemplatesForSelect(int $meesageType, bool $withPlaceholder = true): array
    {
        $options = [];

        if ($withPlaceholder) {
            $options[] = [
                'title' => t('choose'),
                'value' => '',
            ];
        }

        $query = new Template();
        $query->where('status', Template::ACTIVE);

        $parenthesis = new Parenthesis();
        $parenthesis->where('message_type', null, 'is');
        $parenthesis->orWhere('message_type', $meesageType);

        $query->where($parenthesis);

        if ($this->accessedDepartments) {
            $parenthesis = new Parenthesis();
            $parenthesis->where('department_id', null, 'is');
            $parenthesis->orWhere('department_id', array_column($this->accessedDepartments, 'id'), 'in');

            $query->where($parenthesis);
        } else {
            $query->where('department_id', null, 'is');
        }

        $query->ArrayBuilder();

        $templates = $query->get(null, ['id', 'title', 'department_id']);

        foreach ($templates as $template) {
            $options[] = [
                'title' => $template['title'],
                'value' => $template['id'],
                'data' => [
                    'department' => $template['department_id'],
                ],
            ];
        }

        return $options;
    }
}
