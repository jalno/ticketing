<?php
namespace packages\ticketing\events\tickets;
use \packages\base\event;
use \packages\userpanel\user;
use \packages\notifications\notifiable;
use \packages\ticketing\ticket_message;
class add extends event implements notifiable{
	private $message;
	public function __construct(ticket_message $message){
		$this->message = $message;
	}
	public function getMessage():ticket_message{
		return $this->message;
	}
	public static function getName():string{
		return 'ticketing_ticket_add';
	}
	public static function getParameters():array{
		return [ticket_message::class];
	}
	public function getArguments():array{
		return [
			'ticket_message' => $this->getMessage()
		];
	}
	public function getTargetUsers():array{
		$users = [];
		if($this->message->ticket->client->id == $this->message->user->id){
			$parents = $this->message->ticket->client->parentTypes();
			if($parents){
				$user = new user();
				$user->where("type", $parents, 'in');
				$users = array_merge($users, array_column($user->get(null, ['id']), 'id'));
			}
		}else{
			$users[] = $this->message->ticket->client->id;
		}

		$users = array_unique($users);
		$selfUser = array_search($this->message->user->id, $users);
		if($selfUser !== false){
			unset($users[$selfUser]);
		}
		if($users){
			$users = user::where("id", array_values($users), 'in')->get();
		}
		return $users;
	}
}