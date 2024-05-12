<?php

namespace themes\clipone\Views\Ticketing;

use packages\base\HTTP;
use packages\base\Translator;
use packages\base\View\Error;
use packages\base\Views\Traits\Form;
use packages\ticketing\Authentication;
use packages\ticketing\Authorization;
use packages\ticketing\Label;
use packages\ticketing\Ticket;
use packages\ticketing\Views\TicketList as TicketListView;
use packages\userpanel;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\ListTrait;
use themes\clipone\Views\TabTrait;
use themes\clipone\ViewTrait;

class ListView extends TicketListView
{
    use Form;
    use ViewTrait;
    use ListTrait;
    use FormTrait;
    use TabTrait;
    use LabelTrait;

    protected $multiuser;
    protected $hasAccessToUsers = false;

    public static function onSourceLoad()
    {
        parent::onSourceLoad();
        if (parent::$navigation) {
            $item = new MenuItem('ticketing');
            $item->setTitle(t('ticketing'));
            $item->setURL(userpanel\url('ticketing'));
            $item->setIcon('clip-user-6');
            $item->setPriority(280);
            Navigation::addItem($item);
        }
    }

    public function __beforeLoad()
    {
        $this->setTitle([
            t('tickets'),
        ]);
        $this->setButtons();
        $this->onSourceLoad();
        if ($this->isTab) {
            Navigation::active('users');
        } else {
            Navigation::active('ticketing/list');
        }
        $this->addBodyClass('tickets-search');
        $this->multiuser = (bool) Authorization::childrenTypes();
        $this->hasAccessToUsers = Authorization::is_accessed('users_list', 'userpanel');
    }

    public function setButtons()
    {
        $this->setButton('view', $this->canView, [
            'title' => Translator::trans('ticketing.view'),
            'icon' => 'fa fa-credit-card',
            'classes' => ['btn', 'btn-xs', 'btn-green'],
        ]);
        $this->setButton('delete', $this->canDel, [
            'title' => Translator::trans('ticketing.delete'),
            'icon' => 'fa fa-times',
            'classes' => ['btn', 'btn-xs', 'btn-bricky'],
        ]);
    }

    /**
     * Ouput the html file.
     *
     * @return void
     */
    public function output()
    {
        if ($this->isTab) {
            $this->outputTab();
        } else {
            parent::output();
        }
    }

    public function export(): array
    {
        $data = [
            'items' => array_map(function (Ticket $ticket) {
                $data = $ticket->toArray();
                $data['client'] = [
                    'id' => $ticket->client->id,
                    'name' => $ticket->client->name,
                    'lastname' => $ticket->client->lastname,
                ];

                if ($ticket->operator) {
                    $data['operator'] = [
                        'id' => $ticket->operator->id,
                        'name' => $ticket->operator->name,
                        'lastname' => $ticket->operator->lastname,
                    ];
                }

                return $data;
            }, $this->getDataList()),
            'items_per_page' => (int) $this->itemsPage,
            'current_page' => (int) $this->currentPage,
            'total_items' => (int) $this->totalItems,
        ];

        if ($this->canViewLabels) {
            $data['labels'] = array_map(fn (Label $label) => $label->toArray(), $this->labels);
        }

        return ['data' => $data];
    }

    /**
     * @return Label[]
     */
    public function getLabels(array $ids): array
    {
        return array_filter($this->labels, fn (Label $label) => in_array($label->getID(), $ids));
    }

    public function getLabelsForShow(array $ids): string
    {
        return implode(' ', array_map(fn (Label $label) => $this->getLabel($label, $this->isTab ? 'users/tickets/'.$this->getNewTicketClientID() : 'ticketing'), $this->getLabels($ids)));
    }

    protected function getNewTicketURL(): string
    {
        $newTicketClientID = $this->getNewTicketClientID();
        $params = [];
        if ($newTicketClientID) {
            $params['client'] = $newTicketClientID;
        }
        $query = http_build_query($params);

        return userpanel\url('ticketing/new'.($query ? '?'.$query : ''));
    }

    protected function getDepartmentsForSelect()
    {
        $departments = [];
        $departments[0] = [
            'title' => Translator::trans('choose'),
            'value' => '',
        ];
        foreach ($this->getDepartment() as $department) {
            $departments[] = [
                'title' => $department->title,
                'value' => $department->id,
            ];
        }

        return $departments;
    }

    protected function getPriortyForSelect()
    {
        return [
            [
                'title' => Translator::trans('choose'),
                'value' => '',
            ],
            [
                'title' => Translator::trans('instantaneous'),
                'value' => Ticket::instantaneous,
            ],
            [
                'title' => Translator::trans('important'),
                'value' => Ticket::important,
            ],
            [
                'title' => Translator::trans('ordinary'),
                'value' => Ticket::ordinary,
            ],
        ];
    }

    protected function getComparisonsForSelect()
    {
        return [
            [
                'title' => Translator::trans('search.comparison.contains'),
                'value' => 'contains',
            ],
            [
                'title' => Translator::trans('search.comparison.equals'),
                'value' => 'equals',
            ],
            [
                'title' => Translator::trans('search.comparison.startswith'),
                'value' => 'startswith',
            ],
        ];
    }

    protected function isActive($item = 'all'): bool
    {
        $status = $this->getDataForm('status');
        if ($status) {
            sort($status);
        } else {
            $status = [];
        }
        if ('all' == $item) {
            static $allStatus;
            if (!$allStatus) {
                $allStatus = [
                    Ticket::unread,
                    Ticket::read,
                    Ticket::in_progress,
                    Ticket::answered,
                    Ticket::closed,
                ];
                sort($allStatus);
            }

            return $status == $allStatus;
        }
        if ('inProgress' == $item) {
            return $status == [
                Ticket::in_progress,
            ];
        }
        if ('active' == $item) {
            static $activeStatus;
            if (!$activeStatus) {
                $activeStatus = [
                    Ticket::unread,
                    Ticket::read,
                    Ticket::answered,
                    Ticket::in_progress,
                ];
                sort($activeStatus);
            }

            return $status == $activeStatus or (1 == count($status) and in_array($status[0], [Ticket::unread, Ticket::read, Ticket::answered]));
        }
        if ('closed' == $item) {
            return $status == [
                Ticket::closed,
            ];
        }

        return false;
    }

    protected function getOrderedtickets(): array
    {
        $tickets = $this->getTickets();
        if (!$tickets) {
            $tickets = [];
        }
        if (!Authorization::childrenTypes()) {
            return $tickets;
        }
        $ordered = [];
        $user = Authentication::getUser();
        foreach ($tickets as $key => $ticket) {
            if ($ticket->operator_id == $user->id) {
                $ordered[] = $ticket;
                unset($tickets[$key]);
            }
        }
        foreach ($tickets as $ticket) {
            $ordered[] = $ticket;
        }

        return $ordered;
    }

    protected function getTicketStatusForSelect(): array
    {
        return [
            [
                'title' => t('choose'),
                'value' => '',
                'disabled' => true,
            ],
            [
                'title' => t('unread'),
                'value' => Ticket::unread,
            ],
            [
                'title' => t('read'),
                'value' => Ticket::read,
            ],
            [
                'title' => t('in_progress'),
                'value' => Ticket::in_progress,
            ],
            [
                'title' => t('answered'),
                'value' => Ticket::answered,
            ],
            [
                'title' => t('closed'),
                'value' => Ticket::closed,
            ],
        ];
    }

    protected function getPath($params = []): string
    {
        $params = array_merge(HTTP::$data, $params);
        unset($params['page']);

        return '?'.http_build_query($params);
    }

    private function addNotFoundError()
    {
        $error = new Error();
        $error->setType(Error::NOTICE);
        $error->setCode('ticketing.ticket.notfound');
        $error->setData([
            [
                'type' => 'btn-teal',
                'txt' => Translator::trans('ticketing.add'),
                'link' => userpanel\url('ticketing/new'),
            ],
        ], 'btns');
        $this->addError($error);
    }
}
