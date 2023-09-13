<?php
namespace packages\ticketing;

use packages\base\Exception;
use \packages\base\db\dbObject;
use \packages\userpanel\user;
use \packages\userpanel\date;
use \packages\userpanel\user_option;
use \packages\userpanel\usertype_option;

class ticket_message extends dbObject
{
	const unread = 0;
	const read = 1;
	const html = 'html';
	const markdown = 'markdown';

	public static function convertContent(string $content, string $format): string
	{
		switch ($format) {
			case self::markdown:
				return (new Parsedown())->text($content);
			case self::html:
				$content = str_replace(["\r\n", "\n\r", "\r"], "\n", $content);
				$content = preg_replace('@([https|http|ftp]+://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" rel="nofollow">$1</a>', $content);

				return implode("\n", array_map(fn (string $line) => '<p dir="auto">'.("\n" !== $line ? $line : '&nbsp;')."</p>\n", explode("\n", $content)));
			default:
				throw new Exception('Unknown format '.$format.' for convert');
		}
	}

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
		'ticket' => array('hasOne', 'packages\\ticketing\\ticket', 'ticket'),
		'user' => array('hasOne', 'packages\\userpanel\\user', 'user'),
		'files' => array('hasMany', 'packages\\ticketing\\ticket_file', 'message')
	);

	public function getContent(): string
	{
		return self::convertContent($this->text, $this->format);
	}

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
