<?php
namespace packages\ticketing\events\tickets;
use \packages\base\event;
use \packages\userpanel\user;
use \packages\userpanel\authentication;
use \packages\notifications\notifiable;
use \packages\ticketing\ticket;
class close extends event implements notifiable{
	private $ticket;
	public function __construct(ticket $ticket){
		$this->ticket = $ticket;
	}
	public function getTicket():ticket{
		return $this->ticket;
	}
	public static function getName():string{
		return 'ticketing_ticket_close';
	}
	public static function getParameters():array{
		return [ticket::class];
	}
	public function getArguments():array{
		return [
			'ticket' => $this->getTicket()
		];
	}
	public function getTargetUsers():array{
		$users = [];

		$selfUser = authentication::getID();
		if($selfUser and $this->ticket->client->id != $selfUser){
			$users[] = $this->ticket->client;
		}

		$parents = $this->ticket->client->parentTypes();
		if($parents){
			$user = new user();
			$user->where("type", $parents, 'in');
			$user->where("id", $this->ticket->client->id, '!=');
			if($selfUser){
				$user->where("id", $selfUser, '!=');
			}
			$users = array_merge($users, $user->get());
		}
		return $users;
	}
}