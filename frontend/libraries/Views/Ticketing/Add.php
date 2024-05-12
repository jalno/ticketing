<?php

namespace themes\clipone\Views\Ticketing;

use packages\ticketing\Authorization;
use packages\ticketing\Department;
use packages\ticketing\Ticket;
use packages\ticketing\TicketMessage as Message;
use packages\ticketing\Views\Add as TicketAdd;
use packages\userpanel;
use packages\userpanel\Authentication;
use packages\userpanel\User;
use themes\clipone\Breadcrumb;
use themes\clipone\Events\AddingTicket;
use themes\clipone\Navigation;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\Dashboard\Box;
use themes\clipone\Views\Dashboard\Shortcut;
use themes\clipone\Views\FormTrait;
use themes\clipone\ViewTrait;

class Add extends TicketAdd
{
    use ViewTrait;
    use FormTrait;
    use HelperTrait;

    public static $shortcuts = [];
    public static $boxs = [];

    public ?string $messageFormat = null;
    public bool $canUseTemplates = false;
    protected $hasPredefinedClients = false;
    protected $sendNotification = false;

    public function __construct()
    {
        parent::__construct();
        $this->canUseTemplates = Authorization::is_accessed('use_templates');
    }

    public function __beforeLoad(): void
    {
        $this->hasPredefinedClients = !empty($this->getClients());
        $this->sendNotification = Ticket::sendNotificationOnSendTicket($this->canEnableDisableNotification ? Authentication::getUser() : null);
        $this->messageFormat = Authentication::getUser()->getOption('ticketing_editor') ?: Message::html;
        $this->accessedDepartments = $this->getDepartmentData();
        $this->setTitle([
            t('ticketing.add'),
        ]);
        $this->setNavigation();
        $this->setUserInput();
        $initEvent = new AddingTicket();
        $initEvent->view = $this;
        $initEvent->trigger();
        $this->addBodyClass('ticketing');
        $this->addBodyClass('tickets-add');
        $this->setFormData();
    }

    public function getClientsToArray(): array
    {
        $toArray = [];
        foreach ($this->getClients() as $client) {
            $toArray[] = $client->toArray();
        }

        return $toArray;
    }

    private function setNavigation()
    {
        $item = new MenuItem('ticketing');
        $item->setTitle(t('ticketing'));
        $item->setURL(userpanel\url('ticketing'));
        $item->setIcon('clip-paperplane');
        Breadcrumb::addItem($item);

        $item = new MenuItem('ticketing.add');
        $item->setTitle(t('ticketing.add'));
        $item->setIcon('fa fa-add tip');
        Breadcrumb::addItem($item);
        Navigation::active('ticketing/list');
    }

    protected function getDepartmentsForSelect(): array
    {
        $departments = [
            [
                'title' => t('ticketing.choose'),
                'value' => '',
            ],
        ];
        $allProducts = $this->getProductsForSelect();
        $getProductsSelectOptions = function (Department $department) use ($allProducts) {
            $options = [];
            if ($this->hasAccessToIgnoreDepartmentProduct or !$department->isMandatoryChooseProduct()) {
                $options[] = [
                    'title' => t('none'),
                    'value' => '',
                ];
            }
            $departmentProducts = $department->getProducts();
            if ($departmentProducts) {
                foreach ($departmentProducts as $departmentProduct) {
                    foreach ($allProducts as $product) {
                        if ($product['value'] == $departmentProduct) {
                            $options[] = $product;
                        }
                    }
                }
            } else {
                $options = array_merge($options, $allProducts);
            }

            return $options;
        };
        foreach ($this->getDepartmentData() as $row) {
            $departments[] = [
                'title' => $row->title,
                'value' => $row->id,
                'data' => [
                    'working' => $row->isWorking() ? 1 : 0,
                    'products' => $getProductsSelectOptions($row),
                ],
            ];
        }

        return $departments;
    }

    protected function getProductsForSelect(): array
    {
        $products = [];
        foreach ($this->getProducts() as $product) {
            $products[] = [
                'title' => $product->getTitle(),
                'value' => $product->getName(),
            ];
        }

        return $products;
    }

    protected function getpriortyForSelect(): array
    {
        return [
            [
                'title' => t('ordinary'),
                'value' => Ticket::ordinary,
            ],
            [
                'title' => t('important'),
                'value' => Ticket::important,
            ],
            [
                'title' => t('instantaneous'),
                'value' => Ticket::instantaneous,
            ],
        ];
    }

    protected function products(): array
    {
        $none = [[
            'title' => t('none'),
            'value' => '',
        ]];

        return array_merge($none, $this->getProductsForSelect());
    }

    private function setUserInput()
    {
        if ($error = $this->getFormErrorsByInput('client')) {
            $error->setInput('user_name');
            $this->setFormError($error);
        }
        $user = $this->getDataForm('client');
        if ($user and $user = User::byId($user)) {
            $this->setDataForm($user->name, 'user_name');
        }
    }

    public static function addShortcut(Shortcut $shortcut): void
    {
        foreach (self::$shortcuts as $key => $item) {
            if ($item->name == $shortcut->name) {
                self::$shortcuts[$key] = $shortcut;

                return;
            }
        }
        self::$shortcuts[] = $shortcut;
    }

    public static function addBox(Box $box)
    {
        self::$boxs[] = $box;
    }

    public function getBoxs(): array
    {
        return self::$boxs;
    }

    public function generateShortcuts()
    {
        $rows = [];
        $lastrow = 0;
        $shortcuts = array_slice(self::$shortcuts, 0, max(3, floor(count(self::$shortcuts) / 2)));
        foreach ($shortcuts as $box) {
            $rows[$lastrow][] = $box;
            $size = 0;
            foreach ($rows[$lastrow] as $rowbox) {
                $size += $rowbox->size;
            }
            if ($size >= 12) {
                ++$lastrow;
            }
        }
        $html = '';
        foreach ($rows as $row) {
            $html .= '<div class="row">';
            foreach ($row as $shortcut) {
                $html .= "<div class=\"col-sm-{$shortcut->size}\">";
                $html .= '<div class="core-box">';
                $html .= '<div class="heading">';
                $html .= "<i class=\"{$shortcut->icon} circle-icon circle-{$shortcut->color}\"></i>";
                $html .= "<h2>{$shortcut->title}</h2>";
                $html .= '</div>';
                $html .= "<div class=\"content\">{$shortcut->text}</div>";
                $html .= '<a class="view-more" href="'.$shortcut->link[1].'"><i class="clip-arrow-left-2"></i> '.$shortcut->link[0].'</a>';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        return $html;
    }

    public function generateRows()
    {
        $rows = [];
        $lastrow = 0;
        foreach (self::$boxs as $box) {
            $rows[$lastrow][] = $box;
            $size = 0;
            foreach ($rows[$lastrow] as $rowbox) {
                $size += $rowbox->size;
            }
            if ($size >= 12) {
                ++$lastrow;
            }
        }
        $html = '';
        foreach ($rows as $row) {
            $html .= '<div class="row">';
            foreach ($row as $box) {
                $html .= "<div class=\"col-md-{$box->size}\">".$box->getHTML().'</div>';
            }
            $html .= '</div>';
        }

        return $html;
    }

    private function setFormData()
    {
        $this->setDataForm($this->messageFormat, 'message_format');
        $this->setDataForm($this->sendNotification ? 1 : 0, 'send_notification');
        $this->setDataForm($this->hasPredefinedClients ? 1 : 0, 'multiuser_mode');
        $this->setData(!$this->canUseTemplates, 'content_editor_preview_disabled');
    }
}
