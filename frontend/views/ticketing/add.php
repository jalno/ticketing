<?php
namespace themes\clipone\views\ticketing;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;

use \packages\ticketing\views\add as ticketadd;
use \packages\ticketing\ticket;

use \packages\userpanel;

use \themes\clipone\viewTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;


class add extends ticketadd{
	use viewTrait,formTrait;
	protected $department;
	protected $user;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('ticketing.add')
		));
		$this->setShortDescription(translator::trans('newticket'));
		$this->setNavigation();
		$this->SetDataValue();
		$this->addAssets();
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
	protected function SetDataValue(){
		foreach($this->getDepartmentData() as $row){
			$this->department[] = array(
				'title' => $row->title,
				'value' => $row->id
			);
		}
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
}
