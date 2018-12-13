<?php
namespace packages\ticketing\events\tickets;
use \packages\base\event;
use \packages\userpanel\user;
use \packages\notifications\notifiable;
use \packages\ticketing\ticket_message;
class reply extends event implements notifiable{
	private $message;
	public function __construct(ticket_message $message){
		$this->message = $message;
	}
	public function getMessage():ticket_message{
		return $this->message;
	}
	public static function getName():string{
		return 'ticketing_ticket_reply';
	}
	public static function getParameters():array{
		return [ticket_message::class];
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
			$user = new user();
			if ($users) {
				$user->where("id", $users, "IN");
			}
			if ($parents) {
				$user->where("type", $parents, "IN");
			}
			return $user->get();
		} else {
			return user::where("id", $this->message->ticket->client->id)->get();
		}
	}
}