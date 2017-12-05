<?php
namespace packages\ticketing\logs\settings\departments;
use \packages\base\{view, translator};
use \packages\userpanel\{logs\panel, logs};
class edit extends logs{
	public function getColor():string{
		return "circle-teal";
	}
	public function getIcon():string{
		return "fa fa-bank";
	}
	private function timeFormat(int $time):string{
		return translator::trans("ticketing.logs.settings.departments.edit.time.".($time < 12 ? "am" : "pm"), ["time" => $time]);
	}
	public function buildFrontend(view $view){
		$parameters = $this->log->parameters;
		$oldData = $parameters['oldData'];
		$worktimes = isset($oldData['worktimes']) ? $oldData['worktimes'] : [];
		unset($oldData['worktimes']);

		if(!empty($oldData)){
			$panel = new panel('ticketing.logs.settings.departments.edit');
			$panel->icon = 'fa fa-external-link-square';
			$panel->size = 6;
			$panel->title = translator::trans('ticketing.logs.settings.departments.information');
			$html = '<div class="form-group">';
			$html .= '<label class="col-xs-4 control-label">'.translator::trans("department.title").': </label>';
			$html .= '<div class="col-xs-8">'.$oldData['title'].'</div>';
			$html .= "</div>";
			
			$panel->setHTML($html);
			$this->addPanel($panel);
		}

		if(!empty($worktimes)){
			$panel = new panel('ticketing.logs.settings.departments.edit.worktimes');
			$panel->icon = 'fa fa-external-link-square';
			$panel->size = 6;
			$panel->title = translator::trans('ticketing.logs.settings.departments.worktimes');
			$html = '';
			$html = '<div class="table-responsive">';
			$html .= '<table class="table table-striped">';
			$html .= "<thead><tr>";
			$html .= "<th>#</th>";
			$html .= "<th>ساعت شروع کاری</th>";
			$html .= "<th>ساعت پایان کاری</th>";
			$html .= "<th>پیام</th>";
			$html .= "</tr></thead>";
			$html .= "<tbody>";
			foreach($worktimes as $work){
				$html .= "<tr><td>{$work->id}</th>";
				$html .= "<td>".$this->timeFormat($work->time_start)."</td>";
				$html .= "<td>".$this->timeFormat($work->time_end)."</td>";
				$html .= "<td>".$work->message."</td></tr>";
			}
			$html .= "</tbody></table></div>";

			$panel->setHTML($html);
			$this->addPanel($panel);
		}
	}
}
