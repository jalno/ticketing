<?php
namespace themes\clipone\views\ticketing;
use \packages\base\translator;
use \packages\ticketing\views\view as ticketView;
use \packages\userpanel;
use \themes\clipone\views\listTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\utility;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;
use \packages\ticketing\ticket;
use \packages\ticketing\Parsedown;
use \packages\ticketing\products;
class view extends ticketView{
	use viewTrait, listTrait, formTrait;
	protected $messages;
	protected $canSend = true;
	protected $ticket;
	function __beforeLoad(){
		$this->ticket = $this->getTicket();
		$this->setTitle([
			translator::trans('ticketing.view'),
			translator::trans('ticket'),
			"#".$this->ticket->id
		]);
		$this->setShortDescription(translator::trans('ticketing.view').' '.translator::trans('ticket'));
		$this->setNavigation();
		$this->SetDataView();
	}
	private function setNavigation(){
		$item = new menuItem("ticketing");
		$item->setTitle(translator::trans('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-paperplane');
		breadcrumb::addItem($item);

		$item = new menuItem("ticketing.view");
		$item->setTitle($this->ticket->title);
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('fa fa-comment-o');
		breadcrumb::addItem($item);
		navigation::active("ticketing/list");
	}
	protected function SetDataView(){
		$this->messages = $this->ticket->message;
		foreach($this->messages as $message){
			if($message->format == "markdown"){
				$Parsedown = new Parsedown();
				$text = $Parsedown->text($message->text);
			}elseif($message->format == "html"){
				$text = "<p>".($this->formatUrlsInText(nl2br($message->text)))."</p>";
			}
			$message->content = $text;
		}
		if($this->ticket->param('ticket_lock') or $this->ticket->param('ticket_lock') != ticket::canSendMessage){
			$this->canSend = false;
		}
		if($user = $this->getDataForm('client')){
			if($user = userpanel\user::byId($user)){
				$this->setDataForm($user->getFullName(), 'client_name');
			}
		}
		if($error = $this->getFormErrorsByInput('client')){
			$error->setInput('client_name');
			$this->setFormError($error);
		}
	}
	private function formatUrlsInText(string $text):string{
        $reg_exUrl = '/(http|ftp|https):\\/\\/([\\w+?\\.\\w+])+([a-zA-Z0-9\\~\\!\\@\\#\\$\\%\\^\\&\\*\\(\\)_\\-\\=\\+\\\\\\/\\?\\.\\:\\;\\\'\\,]*)?/';
        preg_match_all($reg_exUrl, $text, $matches);
        $usedPatterns = array();
        foreach($matches[0] as $pattern){
            if(!array_key_exists($pattern, $usedPatterns)){
                $usedPatterns[$pattern]=true;
                $text = str_replace($pattern, "<a href=\"{$pattern}\" rel=\"nofollow\">{$pattern}</a> ", $text);
            }
        }
        return $text;
    }
	protected function getProductService(){
		foreach(products::get() as $product){
			if($product->getName() == $this->ticket->param('product')){
				$product->showInformationBox($this->ticket->client, $this->ticket->param('service'));
				return $product;
			}
		}
		return null;
	}
	protected function getDepartmentForSelect(){
		$departments = [];
		foreach($this->getDepartment() as $department){
			$departments[] = [
				'title' => $department->title,
				'value' => $department->id
			];
		}
		return $departments;
	}
	protected function getStatusForSelect(){
		return [
			[
	            'title' => translator::trans('unread'),
	            'value' => ticket::unread
        	],
			[
	            'title' => translator::trans('read'),
	            'value' => ticket::read
        	],
			[
	            'title' => translator::trans('answered'),
	            'value' => ticket::answered
        	],
			[
	            'title' => translator::trans('in_progress'),
	            'value' => ticket::in_progress
        	],
			[
	            'title' => translator::trans('closed'),
	            'value' => ticket::closed
        	]
		];
	}
	protected function getpriortyForSelect(){
		return [
			[
	            'title' => translator::trans('instantaneous'),
	            'value' => ticket::instantaneous
        	],
			[
	            'title' => translator::trans('important'),
	            'value' => ticket::important
        	],
			[
	            'title' => translator::trans('ordinary'),
	            'value' => ticket::ordinary
        	]
		];
	}
}
