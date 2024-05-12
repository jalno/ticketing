<?php
namespace packages\ticketing\Events\Tickets;
use \packages\base\Event;
use \packages\userpanel\User;
use \packages\userpanel\Authentication;
use \packages\notifications\Notifiable;
use \packages\ticketing\Ticket;
class Inprogress extends Event implements Notifiable{
	private $ticket;
	public function __construct(ticket $ticket){
		$this->ticket = $ticket;
	}
	public function getTicket():Ticket{
		return $this->ticket;
	}
	public static function getName():string{
		return 'ticketing_ticket_inprogress';
	}
	public static function getParameters():array{
		return [Ticket::class];
	}
	public function getArguments():array{
		return [
			'ticket' => $this->getTicket()
		];
	}
	public function getTargetUsers():array{
		$users = [];

		$selfUser = Authentication::getID();
		if($selfUser and $this->ticket->client->id != $selfUser){
			$users[] = $this->ticket->client;
		}

		$parents = $this->ticket->client->parentTypes();
		if($parents){
			$user = new User();
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