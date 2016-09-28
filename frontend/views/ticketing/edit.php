<?php
namespace themes\clipone\views\ticketing;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;

use \packages\ticketing\views\edit as ticketEdit;
use \packages\ticketing\ticket;

use \packages\userpanel;

use \themes\clipone\views\formTrait;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;


class edit extends ticketEdit{
	use viewTrait,formTrait;
	protected $department;
	protected $user;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('ticketing.edit'),
			translator::trans('ticket'),
			"#".$this->getTicketData()->id
		));
		$this->setShortDescription(translator::trans('ticketing.edit').' '.translator::trans('ticket'));
		$this->setNavigation();
		$this->SetDataValue();
		$this->getStatusForSelect();
		$this->getpriortyForSelect();
	}
	private function setNavigation(){
		$item = new menuItem("ticketing");
		$item->setTitle(translator::trans('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-paperplane');
		breadcrumb::addItem($item);

		$item = new menuItem("ticketing.edit");
		$item->setTitle(translator::trans('ticketing.edit'));
		$item->setIcon('fa fa-edit tip tooltips');
		breadcrumb::addItem($item);
		navigation::active("ticketing/list");
	}
	protected function SetDataValue(){
		foreach($this->getDepartmentData() as $row){
			$this->department[] = array(
				'title' => $row->title,
				'value' => $row->id
			);
		}
	}
	protected function getStatusForSelect(){
		return array(
			array(
	            'title' => translator::trans('unread'),
	            'value' => ticket::unread
        	),
			array(
	            'title' => translator::trans('read'),
	            'value' => ticket::read
        	),
			array(
	            'title' => translator::trans('answered'),
	            'value' => ticket::answered
        	),
			array(
	            'title' => translator::trans('in_progress'),
	            'value' => ticket::in_progress
        	),
			array(
	            'title' => translator::trans('closed'),
	            'value' => ticket::closed
        	)
		);
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
}
