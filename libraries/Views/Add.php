<?php

namespace packages\ticketing\Views;

use packages\ticketing\Authorization;
use packages\userpanel\User;

class Add extends Form
{
    protected $canSpecifyUser;
    protected $isSelectMultiUser;
    protected $canSpecifyMultiUser;
    protected $canEnableDisableNotification;
    protected $hasAccessToIgnoreDepartmentProduct;

    public function __construct()
    {
        $this->canSpecifyUser = (bool) Authorization::childrenTypes();
        $this->canSpecifyMultiUser = Authorization::is_accessed('add_multiuser');
        $this->canEnableDisableNotification = Authorization::is_accessed('enable_disabled_notification');
        $this->hasAccessToIgnoreDepartmentProduct = Authorization::is_accessed('add_override-force-product-choose');
    }

    public function setClient(User $client): void
    {
        $this->setData($client, 'client');
        $this->setDataForm($client->id, 'client');
        $this->setDataForm($client->getFullName(), 'client_name');
    }

    public function getClient(): ?User
    {
        return $this->getData('client');
    }

    public function setClients(array $clients): void
    {
        if (1 == count($clients) or !$this->getData('multiuser_mode')) {
            $this->setClient($clients[0]);
        } else {
            $this->setData($clients, 'clients');
        }
    }

    public function getClients(): array
    {
        return $this->getData('clients') ?? [];
    }

    public function setMessageData($data)
    {
        $this->setData($data, 'ticket');
    }

    public function getMessageData()
    {
        return $this->getData('ticket');
    }

    public function setDepartmentData($data)
    {
        $this->setData($data, 'department');
    }

    public function getDepartmentData()
    {
        return $this->getData('department');
    }

    public function setProducts($products)
    {
        $this->setData($products, 'products');
    }

    public function getProducts()
    {
        return $this->getData('products') ?? [];
    }

    public function selectMultiUser(bool $isSelect)
    {
        $this->isSelectMultiUser = $isSelect;
    }
}
