<?php
namespace packages\ticketing\views;

use packages\base\views\traits\form as formTrait;
use packages\ticketing\Authorization;
use packages\ticketing\contracts\ILabel;
use packages\ticketing\views\listview as list_view;

class ticketlist extends list_view
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
	static protected $navigation;
	
	function __construct(){
		$this->canAdd = Authorization::is_accessed('add');
		$this->canView = Authorization::is_accessed('view');
		$this->canEdit = Authorization::is_accessed('edit');
		$this->canDel = Authorization::is_accessed('delete');
		$this->multiuser = (bool)Authorization::childrenTypes();
	}
	public function getTickets(): array
	{
		return $this->getDataList();
	}
	public function setNewTicketClientID(int $clientID = 0): void {
		$this->setData($clientID, 'newTicketClientID');
	}
	public function getNewTicketClientID() {
		return $this->getData('newTicketClientID');
	}
	public function setDepartment($department){
		$this->setData($department, 'department');
	}
	public function getDepartment(){
		return $this->getData('department');
	}
	public static function onSourceLoad(){
		self::$navigation = Authorization::is_accessed('list');
	}
	public function isTab(bool $isTab = true): void {
		$this->isTab = $isTab;
	}
}
