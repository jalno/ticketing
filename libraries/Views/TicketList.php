<?php

namespace packages\ticketing\Views;

use packages\ticketing\Authorization;
use packages\ticketing\Contracts\ILabel;

class TicketList extends ListView
{
    /**
     * @var ILabel[]
     */
    public array $labels = [];
    public bool $canViewLabels = false;

    protected $canAdd;
    protected $canView;
    protected $canEdit;
    protected $canDel;
    protected $multiuser;
    protected $isTab = false;

    public function __construct()
    {
        $this->canAdd = Authorization::is_accessed('add');
        $this->canView = Authorization::is_accessed('view');
        $this->canEdit = Authorization::is_accessed('edit');
        $this->canDel = Authorization::is_accessed('delete');
        $this->multiuser = (bool) Authorization::childrenTypes();
    }

    public function getTickets(): array
    {
        return $this->getDataList();
    }

    public function setNewTicketClientID(int $clientID = 0): void
    {
        $this->setData($clientID, 'newTicketClientID');
    }

    public function getNewTicketClientID()
    {
        return $this->getData('newTicketClientID');
    }

    public function setDepartment($department)
    {
        $this->setData($department, 'department');
    }

    public function getDepartment()
    {
        return $this->getData('department');
    }

    public function isTab(bool $isTab = true): void
    {
        $this->isTab = $isTab;
    }
}
