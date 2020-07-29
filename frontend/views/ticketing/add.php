<?php
namespace themes\clipone\views\ticketing;

use packages\ticketing\{Authorization, Department, Ticket};
use packages\userpanel;
use packages\userpanel\User;
use themes\clipone\{Breadcrumb, Navigation, ViewTrait};
use themes\clipone\events\AddingTicket;
use themes\clipone\Navigation\MenuItem;
use themes\clipone\views\FormTrait;
use themes\clipone\views\dashboard\{Box, Shortcut};
use packages\ticketing\views\Add as TicketAdd;

class Add extends TicketAdd {
	use ViewTrait, FormTrait;

	public static $shortcuts = array();
	public static $boxs = array();
	protected $multiuser = false;

	function __beforeLoad() {
		$this->setTitle(array(
			t('ticketing.add')
		));
		$this->setNavigation();
		$this->setUserInput();
		$initEvent = new AddingTicket();
		$initEvent->view = $this;
		$initEvent->trigger();
		$this->multiuser = (bool)Authorization::childrenTypes();
		$this->addBodyClass("ticketing");
		$this->addBodyClass("tickets-add");
	}
	private function setNavigation() {
		$item = new MenuItem("ticketing");
		$item->setTitle(t('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-paperplane');
		Breadcrumb::addItem($item);

		$item = new MenuItem("ticketing.add");
		$item->setTitle(t('ticketing.add'));
		$item->setIcon('fa fa-add tip');
		Breadcrumb::addItem($item);
		Navigation::active("ticketing/list");
	}
	protected function getDepartmentsForSelect(): array {
		$departments = array(
			array(
				'title' => t("ticketing.choose"),
				'value' => "",
			)
		);
		$allProducts = $this->getProductsForSelect();
		$getProductsSelectOptions = function (Department $department) use ($allProducts) {
			$options = array();
			if (!$department->isMandatoryChooseProduct()) {
				$options[] = array(
					'title' => t('none'),
					'value' => '',
				);
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
			$departments[] = array(
				'title' => $row->title,
				'value' => $row->id,
				'data' => array(
					'working' => $row->isWorking() ? 1 : 0,
					'products' => $getProductsSelectOptions($row),
				)
			);
		}
		return $departments;
	}
	protected function getProductsForSelect(): array {
		$products = array();
		foreach ($this->getProducts() as $product) {
			$products[] = array(
				'title' => $product->getTitle(),
				'value' => $product->getName()
			);
		}
		return $products;
	}
	protected function getpriortyForSelect(): array {
		return array(
			array(
	            "title" => t("ordinary"),
	            "value" => Ticket::ordinary
        	),
			array(
	            "title" => t("important"),
	            "value" => Ticket::important
        	),
			array(
	            "title" => t("instantaneous"),
	            "value" => Ticket::instantaneous
        	),
		);
	}
	protected function products(): array {
		$none = array(array(
			'title' => t('none'),
			'value' => ''
		));
		return array_merge($none, $this->getProductsForSelect());
	}
	private function setUserInput() {
		if ($error = $this->getFormErrorsByInput('client')) {
			$error->setInput('user_name');
			$this->setFormError($error);
		}
		$user = $this->getDataForm('client');
		if ($user and $user = User::byId($user)) {
			$this->setDataForm($user->name, 'user_name');
		}
	}
	public static function addShortcut(Shortcut $shortcut): void {
		foreach (self::$shortcuts as $key => $item) {
			if ($item->name == $shortcut->name) {
				self::$shortcuts[$key] = $shortcut;
				return;
			}
		}
		self::$shortcuts[] = $shortcut;
	}
	public static function addBox(Box $box) {
		self::$boxs[] = $box;
	}
	public function getBoxs(): array {
		return self::$boxs;
	}
	public function generateShortcuts() {
		$rows = array();
		$lastrow = 0;
		$shortcuts = array_slice(self::$shortcuts, 0, max(3, floor(count(self::$shortcuts)/2)));
		foreach ($shortcuts as $box) {
			$rows[$lastrow][] = $box;
			$size = 0;
			foreach ($rows[$lastrow] as $rowbox) {
				$size += $rowbox->size;
			}
			if ($size >= 12) {
				$lastrow++;
			}
		}
		$html = '';
		foreach ($rows as $row) {
			$html .= "<div class=\"row\">";
			foreach ($row as $shortcut) {
				$html .= "<div class=\"col-sm-{$shortcut->size}\">";
				$html .= "<div class=\"core-box\">";
				$html .= "<div class=\"heading\">";
				$html .= "<i class=\"{$shortcut->icon} circle-icon circle-{$shortcut->color}\"></i>";
				$html .= "<h2>{$shortcut->title}</h2>";
				$html .= "</div>";
				$html .= "<div class=\"content\">{$shortcut->text}</div>";
				$html .= "<a class=\"view-more\" href=\"".$shortcut->link[1]."\"><i class=\"clip-arrow-left-2\"></i> ".$shortcut->link[0]."</a>";
				$html .= "</div>";
				$html .= "</div>";
			}
			$html .= "</div>";
		}
		return $html;
	}
	public function generateRows() {
		$rows = array();
		$lastrow = 0;
		foreach (self::$boxs as $box) {
			$rows[$lastrow][] = $box;
			$size = 0;
			foreach ($rows[$lastrow] as $rowbox) {
				$size += $rowbox->size;
			}
			if ($size >= 12) {
				$lastrow++;
			}
		}
		$html = '';
		foreach ($rows as $row) {
			$html .= "<div class=\"row\">";
			foreach($row as $box){
				$html .= "<div class=\"col-md-{$box->size}\">".$box->getHTML()."</div>";
			}
			$html .= "</div>";
		}
		return $html;
	}
}
