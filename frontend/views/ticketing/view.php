<?php
namespace themes\clipone\views\ticketing;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;

use \packages\ticketing\views\view as ticketView;

use \packages\userpanel;

use \themes\clipone\views\listTrait;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;

use \packages\ticketing\ticket;
use \packages\ticketing\Parsedown;

class view extends ticketView{
	use viewTrait,listTrait;
	protected $messages;
	protected $canSend = true;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('ticketing.view'),
			translator::trans('ticket'),
			"#".$this->getTicketData()->id
		));
		$this->setShortDescription(translator::trans('ticketing.view').' '.translator::trans('ticket'));
		$this->setNavigation();
		$this->addAssets();
		$this->SetDataView();
	}
	private function setNavigation(){
		$item = new menuItem("ticketing");
		$item->setTitle(translator::trans('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-paperplane');
		breadcrumb::addItem($item);

		$item = new menuItem("ticketing.view");
		$item->setTitle($this->getTicketData()->title);
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('fa fa-comment-o');
		breadcrumb::addItem($item);
		navigation::active("ticketing/list");
	}
	private function addAssets(){
		$this->addCSSFile(theme::url('assets/css/custom.css'));
	}
	protected function SetDataView(){
		$this->messages = $this->getTicketData()->message;
		foreach($this->messages as $message){
			$date = time()-$message->date;
			if($date == 0){
				$lasTime = translator::trans("just.now");
			}elseif($date < 60){
				$lasTime = $date.translator::trans("lastSec");
			}elseif($date >= 60 and $date < 3600){
				$lasTime = floor($date/60).translator::trans("lastMin");
			}elseif($date >= 3600){
				$lasTime = floor($date/3600).translator::trans("lastHov");
			}
			$message->lastime = $lasTime;

			if($message->format == "markdown"){
				$Parsedown = new Parsedown();
				$text = $Parsedown->text($message->text);
			}elseif($message->format == "html"){
				$text = "<p>".(nl2br($message->text))."</p>";
			}
			$message->content = $text;
		}
		if($this->getTicketData()->param('ticket_lock') or $this->getTicketData()->param('ticket_lock') != ticket::canSendMessage){
			$this->canSend = false;
		}

	}
}
