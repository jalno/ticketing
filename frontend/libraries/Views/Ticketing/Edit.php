<?php

namespace themes\clipone\Views\Ticketing;

use packages\base\Translator;
use packages\ticketing\Label;
use packages\ticketing\Ticket;
use packages\ticketing\Views\Edit as TicketEdit;
use packages\userpanel\User;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\ViewTrait;

class Edit extends TicketEdit
{
    use ViewTrait;
    use FormTrait;

    public Ticket $ticket;

    public function __beforeLoad()
    {
        $this->ticket = $this->getTicket();
        $this->setTitle([
            t('ticketing.edit'),
            t('ticket'),
            '#'.$this->ticket->id,
        ]);
        $this->setShortDescription(t('ticketing.edit').' '.t('ticket'));
        $this->setNavigation();
        $this->setFormData();
    }

    public function export(): array
    {
        $ticket = $this->getTicket();

        $data = [
            'ticket' => $ticket->toArray(),
        ];

        $data['ticket']['client'] = [
            'id' => $ticket->client->id,
            'name' => $ticket->client->name,
            'lastname' => $ticket->client->lastname,
        ];

        if ($ticket->operator) {
            $data['ticket']['operator'] = [
                'id' => $ticket->operator->id,
                'name' => $ticket->operator->name,
                'lastname' => $ticket->operator->lastname,
            ];
        }

        if ($ticket->labels) {
            $data['ticket']['labels'] = array_map(fn (Label $label) => [
                'id' => $label->getID(),
                'title' => $label->getTitle(),
                'description' => $label->getDescription() ?? '',
                'color' => $label->getColor(),
            ], $ticket->labels);
        }

        return ['data' => $data];
    }

    private function setFormData()
    {
        if ($user = $this->getDataForm('client')) {
            if ($user = User::byId($user)) {
                $this->setDataForm($user->getFullName(), 'client_name');
            }
        }
    }

    private function setNavigation()
    {
        Navigation::active('ticketing/list');
    }

    protected function getDepartmentForSelect()
    {
        $departments = [];
        foreach ($this->getDepartment() as $department) {
            $departments[] = [
                'title' => $department->title,
                'value' => $department->id,
            ];
        }

        return $departments;
    }

    protected function getStatusForSelect()
    {
        return [
            [
                'title' => t('unread'),
                'value' => Ticket::unread,
            ],
            [
                'title' => t('read'),
                'value' => Ticket::read,
            ],
            [
                'title' => t('answered'),
                'value' => Ticket::answered,
            ],
            [
                'title' => t('in_progress'),
                'value' => Ticket::in_progress,
            ],
            [
                'title' => t('closed'),
                'value' => Ticket::closed,
            ],
        ];
    }

    protected function getpriortyForSelect()
    {
        return [
            [
                'title' => t('instantaneous'),
                'value' => Ticket::instantaneous,
            ],
            [
                'title' => t('important'),
                'value' => Ticket::important,
            ],
            [
                'title' => t('ordinary'),
                'value' => Ticket::ordinary,
            ],
        ];
    }
}
