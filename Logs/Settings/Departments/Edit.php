<?php

namespace packages\ticketing\Logs\Settings\Departments;

use packages\base\Translator;
use packages\base\View;
use packages\userpanel\Logs;
use packages\userpanel\Logs\Panel;

class Edit extends Logs
{
    public function getColor(): string
    {
        return 'circle-teal';
    }

    public function getIcon(): string
    {
        return 'fa fa-bank';
    }

    private function timeFormat(int $time): string
    {
        return Translator::trans('ticketing.logs.settings.departments.edit.time.'.($time < 12 ? 'am' : 'pm'), ['time' => $time]);
    }

    public function buildFrontend(View $view)
    {
        $parameters = $this->log->parameters;
        $oldData = $parameters['oldData'];
        $worktimes = isset($oldData['worktimes']) ? $oldData['worktimes'] : [];
        unset($oldData['worktimes']);

        if (!empty($oldData) and isset($oldData['title'])) {
            $panel = new Panel('ticketing.logs.settings.departments.edit');
            $panel->icon = 'fa fa-external-link-square';
            $panel->size = 6;
            $panel->title = Translator::trans('ticketing.logs.settings.departments.information');
            $html = '<div class="form-group">';
            $html .= '<label class="col-xs-4 control-label">'.Translator::trans('department.title').': </label>';
            $html .= '<div class="col-xs-8">'.$oldData['title'].'</div>';
            $html .= '</div>';

            $panel->setHTML($html);
            $this->addPanel($panel);
        }

        if (!empty($worktimes)) {
            $panel = new Panel('ticketing.logs.settings.departments.edit.worktimes');
            $panel->icon = 'fa fa-external-link-square';
            $panel->size = 6;
            $panel->title = Translator::trans('ticketing.logs.settings.departments.worktimes');
            $html = '';
            $html = '<div class="table-responsive">';
            $html .= '<table class="table table-striped">';
            $html .= '<thead><tr>';
            $html .= '<th>#</th>';
            $html .= '<th>'.t('ticketing.logs.department.start_hour').'</th>';
            $html .= '<th>'.t('ticketing.logs.department.end_hour').'</th>';
            $html .= '<th>'.t('ticketing.logs.department.message').'</th>';
            $html .= '</tr></thead>';
            $html .= '<tbody>';
            foreach ($worktimes as $work) {
                $html .= "<tr><td>{$work->id}</th>";
                $html .= '<td>'.$this->timeFormat($work->time_start).'</td>';
                $html .= '<td>'.$this->timeFormat($work->time_end).'</td>';
                $html .= '<td>'.$work->message.'</td></tr>';
            }
            $html .= '</tbody></table></div>';

            $panel->setHTML($html);
            $this->addPanel($panel);
        }
    }
}
