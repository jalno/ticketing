<?php
namespace packages\ticketing;
use \packages\base\db\dbObject;
use \packages\userpanel\user;
use \packages\userpanel\date;
use \packages\userpanel\user_option;
use \packages\userpanel\usertype_option;
class ticket_message extends dbObject{
	const unread = 0;
	const read = 1;
	const html = 'html';
	protected $dbTable = "ticketing_tickets_msgs";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'ticket' => array('type' => 'int', 'required' => true),
        'date' => array('type' => 'int', 'required' => true),
		'user' => array('type' => 'int', 'required' => true),
        'text' => array('type' => 'text', 'required' => true),
        'format' => array('type' => 'text', 'required' => true),
		'status' => array('type' => 'int', 'required' => true)
    );
	protected $relations = array(
		'user' => array('hasOne', 'packages\\userpanel\\user', 'user'),
		'files' => array('hasMany', 'packages\\ticketing\\ticket_file', 'message')
	);
	protected function preLoad($data){
		if(!isset($data['format'])){
			$user = user::where('id', $data['user'])->getOne();
			$data['format'] = $user->option('ticketing_editor');
			if(!$data['format']){
				$data['format'] = self::html;
			}
		}
		if(!isset($data['date'])){
			$data['date'] = date::time();
		}

		return $data;
	}
	protected $tmpfiles = array();
	protected function addFile($filedata){
		$file = new ticket_file($filedata);
		if ($this->isNew){
			$this->tmpfiles[] = $file;
			return true;
		}else{
			$file->message = $this->id;
			$return = $file->save();
			if(!$return){
				return false;
			}
			return $return;
		}
	}
	public function save($data = null){
		if(($return = parent::save($data))){
			foreach($this->tmpfiles as $file){
				$file->message = $this->id;
				$file->save();
			}
			$this->tmpfiles = array();
		}
		return $return;
	}
	public function delete(){
		foreach($this->files as $file){
			$file->delete();
		}
		parent::delete();
	}
}
