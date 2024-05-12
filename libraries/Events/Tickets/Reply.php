<?php
namespace packages\ticketing\Events\Tickets;
use \packages\base\Event;
use \packages\userpanel\User;
use \packages\notifications\Notifiable;
use \packages\ticketing\TicketMessage;
class Reply extends Event implements Notifiable{
	private $message;
	public function __construct(TicketMessage $message){
		$this->message = $message;
	}
	public function getMessage():TicketMessage{
		return $this->message;
	}
	public static function getName():string{
		return 'ticketing_ticket_reply';
	}
	public static function getParameters():array{
		return [TicketMessage::class];
	}
	public function getArguments():array{
		return [
			'ticket_message' => $this->getMessage()
		];
	}
	public function getTargetUsers(): array {
		if ($this->message->ticket->client->id == $this->message->user->id) {
			$users = $this->message->ticket->department->users;
			$parents = $this->message->ticket->client->parentTypes();
			if (empty($users) and empty($parents)) {
				return array();
			}
			$user = new User();
			if ($users) {
				$user->where("id", $users, "IN");
			}
			if ($parents) {
				$user->where("type", $parents, "IN");
			}
			return $user->get();
		} else {
			return User::where("id", $this->message->ticket->client->id)->get();
		}
	}
}