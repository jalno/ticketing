<?php
namespace themes\clipone\views\ticketing;
use \packages\base\translator;
use \packages\ticketing\views\add as ticketadd;
use \packages\ticketing\ticket;
use \packages\ticketing\authorization;
use \packages\userpanel;
use \packages\userpanel\user;
use \themes\clipone\viewTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;
use \themes\clipone\events\addingTicket;
use \themes\clipone\views\dashboard\box;
use \themes\clipone\views\dashboard\shortcut;
class add extends ticketadd{
	use viewTrait, formTrait;
	public static $shortcuts = array();
	public static $boxs = array();
	protected $multiuser = false;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('ticketing.add')
		));
		$this->setNavigation();
		$this->setUserInput();
		$initEvent = new addingTicket();
		$initEvent->view = $this;
		$initEvent->trigger();
		$this->multiuser = (bool)authorization::childrenTypes();
	}
	private function setNavigation(){
		$item = new menuItem("ticketing");
		$item->setTitle(translator::trans('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-paperplane');
		breadcrumb::addItem($item);

		$item = new menuItem("ticketing.add");
		$item->setTitle(translator::trans('ticketing.add'));
		$item->setIcon('fa fa-add tip');
		breadcrumb::addItem($item);
		navigation::active("ticketing/list");
	}
	protected function getDepartmentsForSelect(){
		$options = array();
		foreach($this->getDepartmentData() as $row){
			$options[] = array(
				'title' => $row->title,
				'value' => $row->id,
				'data' => array(
					'working' => $row->isWorking() ? 1 : 0
				)
			);
		}
		return $options;
	}
	protected function getProductsForSelect(){
		$products = array();
		foreach($this->getProducts() as $product){
			$products[] = array(
				'title' => $product->getTitle(),
				'value' => $product->getName()
			);
		}
		return $products;
	}
	protected function getpriortyForSelect(){
		return array(
			array(
	            "title" => translator::trans("ordinary"),
	            "value" => ticket::ordinary
        	),
			array(
	            "title" => translator::trans("important"),
	            "value" => ticket::important
        	),
			array(
	            "title" => translator::trans("instantaneous"),
	            "value" => ticket::instantaneous
        	),
		);
	}
	protected function products(){
		$none = array(
			array(
				'title' => translator::trans('none'),
				'value' => ''
		));
		return array_merge($none, $this->getProductsForSelect());
	}
	private function setUserInput(){
		if($error = $this->getFormErrorsByInput('client')){
			$error->setInput('user_name');
			$this->setFormError($error);
		}
		$user = $this->getDataForm('client');
		if($user and $user = user::byId($user)){
			$this->setDataForm($user->name, 'user_name');
		}
	}
	public static function addShortcut(shortcut $shortcut){
		foreach(self::$shortcuts as $key => $item){
			if($item->name == $shortcut->name){
				self::$shortcuts[$key] = $shortcut;
				return;
			}
		}
		self::$shortcuts[] = $shortcut;
	}
	public static function addBox(box $box){
		self::$boxs[] = $box;
	}
	public function getBoxs(){
		return self::$boxs;
	}
	public function generateShortcuts(){
		$rows = array();
		$lastrow = 0;
		$shortcuts = array_slice(self::$shortcuts, 0, max(3, floor(count(self::$shortcuts)/2)));
		foreach($shortcuts as $box){
			$rows[$lastrow][] = $box;
			$size = 0;
			foreach($rows[$lastrow] as $rowbox){
				$size += $rowbox->size;
			}
			if($size >= 12){
				$lastrow++;
			}
		}
		$html = '';
		foreach($rows as $row){
			$html .= "<div class=\"row\">";
			foreach($row as $shortcut){
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
	public function generateRows(){
		$rows = array();
		$lastrow = 0;
		foreach(self::$boxs as $box){
			$rows[$lastrow][] = $box;
			$size = 0;
			foreach($rows[$lastrow] as $rowbox){
				$size += $rowbox->size;
			}
			if($size >= 12){
				$lastrow++;
			}
		}
		$html = '';
		foreach($rows as $row){
			$html .= "<div class=\"row\">";
			foreach($row as $box){
				$html .= "<div class=\"col-md-{$box->size}\">".$box->getHTML()."</div>";
			}
			$html .= "</div>";
		}
		return $html;
	}
}
