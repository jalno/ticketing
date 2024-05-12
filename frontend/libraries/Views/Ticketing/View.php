<?php

namespace themes\clipone\Views\Ticketing;

use packages\ticketing\Authorization;
use packages\ticketing\Department;
use packages\ticketing\Label;
use packages\ticketing\Products;
use packages\ticketing\Ticket;
use packages\ticketing\TicketMessage as Message;
use packages\ticketing\Views\View as TicketView;
use packages\userpanel;
use themes\clipone\Breadcrumb;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\FormTrait;
use themes\clipone\Views\ListTrait;
use themes\clipone\ViewTrait;

class View extends TicketView
{
    use ViewTrait;
    use ListTrait;
    use FormTrait;
    use HelperTrait;
    use LabelTrait {
        LabelTrait::getStatusForSelect as getLabelStatusForSelect;
    }

    public bool $sendNotification = false;
    public bool $canUseTemplates = false;
    public string $messageFormat = Message::html;

    protected $messages;
    protected $canSend = true;
    protected $isLocked = false;
    protected $ticket;
    protected $types = [];

    public function __construct()
    {
        parent::__construct();
        $this->canUseTemplates = Authorization::is_accessed('use_templates');
    }

    public function __beforeLoad()
    {
        $this->ticket = $this->getTicket();
        $this->sendNotification = Ticket::sendNotificationOnSendTicket($this->canEnableDisableNotification ? userpanel\Authentication::getUser() : null);
        $this->accessedDepartments = [$this->ticket->department];
        $this->messageFormat = userpanel\Authentication::getUser()->getOption('ticketing_editor') ?: Message::html;

        $this->setTitle([
            t('ticketing.view'),
            t('ticket'),
            '#'.$this->ticket->id,
        ]);

        $this->setShortDescription(t('ticketing.view').' '.t('ticket'));
        $this->setNavigation();
        $this->SetDataView();
        $this->addBodyClass('ticketing');
        $this->addBodyClass('tickets-view');

        $this->types = Authorization::childrenTypes();
        $this->addPageData();
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

        if ($this->canViewLabels) {
            $data['ticket']['labels'] = array_map(fn (Label $label) => [
                'id' => $label->getID(),
                'title' => $label->getTitle(),
                'description' => $label->getDescription() ?? '',
                'color' => $label->getColor(),
            ], $ticket->labels);
        }

        return ['data' => $data];
    }

    /**
     * @return array{string,bool}
     */
    public function getLabelsPermissions(): array
    {
        return [
            'can_search' => Authorization::is_accessed('settings_labels_search'),
            'can_add' => Authorization::is_accessed('settings_labels_add'),
            'can_delete' => Authorization::is_accessed('settings_labels_delete'),
        ];
    }

    private function setNavigation()
    {
        $item = new MenuItem('ticketing');
        $item->setTitle(t('ticketing'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('clip-paperplane');
        Breadcrumb::addItem($item);

        $item = new MenuItem('ticketing.view');
        $item->setTitle($this->ticket->title);
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('fa fa-comment-o');
        Breadcrumb::addItem($item);
        Navigation::active('ticketing/list');
    }

    protected function SetDataView()
    {
        $this->messages = $this->ticket->message;
        if ($this->ticket->param('ticket_lock') or Ticket::canSendMessage != $this->ticket->param('ticket_lock')) {
            $this->isLocked = true;
        }
        $this->canSend = (
            Authorization::is_accessed('reply')
            and !$this->isLocked
            and Department::ACTIVE == $this->ticket->department->status
        );
        $this->setData(!$this->canSend, 'ticketing_editor_disabled');
        $this->setData(!$this->canUseTemplates, 'content_editor_preview_disabled');

        if (!$this->getDataForm('message_format')) {
            $this->setDataForm($this->messageFormat, 'message_format');
        }

        if ($user = $this->getDataForm('client')) {
            if ($user = userpanel\User::byId($user)) {
                $this->setDataForm($user->getFullName(), 'client_name');
            }
        }
        if ($user = $this->getDataForm('operator_id')) {
            if ($user = userpanel\User::byId($user)) {
                $this->setDataForm($user->getFullName(), 'operator_name');
                $this->setDataForm($user->id, 'operator');
            }
        }
        if ($error = $this->getFormErrorsByInput('client')) {
            $error->setInput('client_name');
            $this->setFormError($error);
        }
        $this->setDataForm($this->sendNotification ? 1 : 0, 'send_notification');
    }

    protected function hasAccessToUser(userpanel\User $other): bool
    {
        $type = $other->data['type'];

        if ($type instanceof userpanel\UserType) {
            $type = $type->id;
        }

        return in_array($type, $this->types);
    }

    protected function getProductService()
    {
        foreach (Products::get() as $product) {
            if ($product->getName() == $this->ticket->param('product')) {
                $product->showInformationBox($this->ticket->client, $this->ticket->param('service'));

                return $product;
            }
        }

        return null;
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

    private function addPageData(): void
    {
        if ($this->canEdit) {
            $this->dynamicData->setData('packages_ticketing_labels', array_map(fn (Label $label) => [
                'id' => $label->getID(),
                'title' => $label->getTitle(),
                'color' => $label->getColor(),
                'description' => $label->getDescription() ?: '',
            ], $this->getAllLabels()));
        }
    }
}
