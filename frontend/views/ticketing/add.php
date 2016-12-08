<?php
namespace themes\clipone\views\ticketing;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;

use \packages\ticketing\views\add as ticketadd;
use \packages\ticketing\ticket;

use \packages\userpanel;
use \packages\userpanel\user;

use \themes\clipone\viewTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;


class add extends ticketadd{
	use viewTrait,formTrait;
	protected $user;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('ticketing.add')
		));
		$this->setNavigation();
		$this->addAssets();
		$this->setUserInput();


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
	private function addAssets(){
		$this->addCSSFile(theme::url('assets/css/custom.css'));
		$this->addJSFile(theme::url('assets/js/pages/ticket.add.js'));
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
	            'title' => translator::trans('instantaneous'),
	            'value' => ticket::instantaneous
        	),
			array(
	            'title' => translator::trans('important'),
	            'value' => ticket::important
        	),
			array(
	            'title' => translator::trans('ordinary'),
	            'value' => ticket::ordinary
        	)
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
}
