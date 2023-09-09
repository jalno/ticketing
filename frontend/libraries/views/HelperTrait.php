<?php

namespace themes\clipone\views\ticketing;

use packages\ticketing\ticket_message as Message;
use themes\clipone\Navigation;
use themes\clipone\navigation\MenuItem;

/**
 * @phpstan-type SelectOptionType array{title:string,value:string,int}
 */
trait HelperTrait
{
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
}
