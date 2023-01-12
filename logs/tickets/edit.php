<?php
namespace packages\ticketing\logs\tickets;
use \packages\base\{view, translator};
use \packages\userpanel\{logs\panel, logs};
use \packages\ticketing\ticket;
class edit extends logs{
	public function getColor():string{
		return "circle-teal";
	}
	public function getIcon():string{
		return "fa fa-ticket";
	}
	private function getStatusTranslate(int $status): string
	{
		switch($status){
			case(ticket::unread):
				return translator::trans('unread');
			case(ticket::read):
				return translator::trans('read');
			case(ticket::answered):
				return translator::trans('answered');
			case(ticket::in_progress):
				return translator::trans('in_progress');
			case(ticket::closed):
				return translator::trans('closed');
			default:
				throw new \Exception('Status is invalid');
		}
	}
	private function getPriorityTranslate(int $priority): string
	{
		switch($priority){
			case(ticket::instantaneous):
				return translator::trans('instantaneous');
			case(ticket::important):
				return translator::trans('important');
			case(ticket::answered):
				return translator::trans('answered');
			case(ticket::ordinary):
				return translator::trans('ordinary');
			default:
				throw new \Exception('Priority is invalid');
		}
	}
	public function buildFrontend(view $view){
		$parameters = $this->log->parameters;
		$oldData = $parameters['oldData'];
		if(!empty($oldData)){
			$panel = new panel('ticketing.logs.ticket.edit');
			$panel->icon = 'fa fa-external-link-square';
			$panel->size = 6;
			$panel->title = translator::trans('ticketing.logs.ticket.information');
			$html = '';
			if(isset($oldData['client'])){
				$html .= '<div class="form-group">';
				$html .= '<label class="col-xs-4 control-label">'.translator::trans("ticket.client").': </label>';
				$html .= '<div class="col-xs-8">'.$oldData['client']->getFullName().'</div>';
				$html .= "</div>";
				unset($oldData['client']);
			}
			if(isset($oldData['department'])){
				$html .= '<div class="form-group">';
				$html .= '<label class="col-xs-4 control-label">'.translator::trans("ticket.department").': </label>';
				$html .= '<div class="col-xs-8">'.$oldData['department']->title.'</div>';
				$html .= "</div>";
				unset($oldData['department']);
			}
			if(isset($oldData['status'])){
				$html .= '<div class="form-group">';
				$html .= '<label class="col-xs-4 control-label">'.translator::trans("ticket.status").': </label>';
				$html .= '<div class="col-xs-8">'.$this->getStatusTranslate($oldData['status']).'</div>';
				$html .= "</div>";
				unset($oldData['status']);
			}
			if(isset($oldData['priority'])){
				$html .= '<div class="form-group">';
				$html .= '<label class="col-xs-4 control-label">'.translator::trans("ticket.priority").': </label>';
				$html .= '<div class="col-xs-8">'.$this->getPriorityTranslate($oldData['priority']).'</div>';
				$html .= "</div>";
				unset($oldData['priority']);
			}
			if(isset($oldData['message'])){
				$html .= '<div class="form-group">';
				$html .= '<label class="col-xs-4 control-label">'.translator::trans("message").': </label>';
				$html .= '<div class="col-xs-8 ltr">#'.$oldData['message']->id.'</div>';
				$html .= "</div>";
				unset($oldData['message']);
			}
			foreach($oldData as $field => $val){
				$html .= '<div class="form-group">';
				$html .= '<label class="col-xs-4 control-label">'.translator::trans("ticket.{$field}").': </label>';
				$html .= '<div class="col-xs-8">'.$val.'</div>';
				$html .= "</div>";
			}
			
			$panel->setHTML($html);
			$this->addPanel($panel);
		}
	}
}
