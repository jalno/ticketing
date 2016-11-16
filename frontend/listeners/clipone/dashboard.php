<?php
namespace themes\clipone\listeners\ticketing;
use \packages\base;
use \packages\base\translator;
use \packages\userpanel;
use \packages\ticketing\authorization;
use \themes\clipone\views\dashboard as view;
use \themes\clipone\views\dashboard\shortcut;
class dashboard{
	public function initialize(){
		$this->addShortcuts();
	}
	protected function addShortcuts(){
		if(authorization::is_accessed('list')){
			$shortcut = new shortcut("tickets");
			$shortcut->icon = 'clip-user-6';
			$shortcut->color = shortcut::teal;
			$shortcut->title = translator::trans('shortcut.tickets.title');
			$shortcut->text = translator::trans('shortcut.tickets.text');
			$shortcut->setLink(translator::trans('shortcut.tickets.link'), userpanel\url('ticketing'));
			view::addShortcut($shortcut);
		}
	}
}
