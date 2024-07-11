<?php

namespace packages\ticketing\Logs\Tickets;

use packages\base\Translator;
use packages\base\View;
use packages\ticketing\Label;
use packages\ticketing\Ticket;
use packages\userpanel\Logs;
use packages\userpanel\Logs\Panel;
use themes\clipone\Views\Ticketing\LabelTrait;

class Edit extends Logs
{
    use LabelTrait;

    public function getColor(): string
    {
        return 'circle-teal';
    }

    public function getIcon(): string
    {
        return 'fa fa-ticket';
    }

    private function getStatusTranslate(int $status): string
    {
        switch ($status) {
            case Ticket::unread:
                return Translator::trans('unread');
            case Ticket::read:
                return Translator::trans('read');
            case Ticket::answered:
                return Translator::trans('answered');
            case Ticket::in_progress:
                return Translator::trans('in_progress');
            case Ticket::closed:
                return Translator::trans('closed');
            default:
                throw new \Exception('Status is invalid');
        }
    }

    private function getPriorityTranslate(int $priority): string
    {
        switch ($priority) {
            case Ticket::instantaneous:
                return Translator::trans('instantaneous');
            case Ticket::important:
                return Translator::trans('important');
            case Ticket::answered:
                return Translator::trans('answered');
            case Ticket::ordinary:
                return Translator::trans('ordinary');
            default:
                throw new \Exception('Priority is invalid');
        }
    }

    public function buildFrontend(View $view)
    {
        $parameters = $this->log->parameters;
        $oldData = $parameters['oldData'];
        if (!empty($oldData)) {
            $panel = new Panel('ticketing.logs.ticket.edit');
            $panel->icon = 'fa fa-external-link-square';
            $panel->size = 6;
            $panel->title = Translator::trans('ticketing.logs.ticket.information');
            $html = '';
            if (isset($oldData['client'])) {
                $html .= '<div class="form-group">';
                $html .= '<label class="col-xs-4 control-label">'.Translator::trans('ticket.client').': </label>';
                $html .= '<div class="col-xs-8">'.$oldData['client']->getFullName().'</div>';
                $html .= '</div>';
                unset($oldData['client']);
            }
            if (isset($oldData['department'])) {
                $html .= '<div class="form-group">';
                $html .= '<label class="col-xs-4 control-label">'.Translator::trans('ticket.department').': </label>';
                $html .= '<div class="col-xs-8">'.$oldData['department']->title.'</div>';
                $html .= '</div>';
                unset($oldData['department']);
            }
            if (isset($oldData['status'])) {
                $html .= '<div class="form-group">';
                $html .= '<label class="col-xs-4 control-label">'.Translator::trans('ticket.status').': </label>';
                $html .= '<div class="col-xs-8">'.$this->getStatusTranslate($oldData['status']).'</div>';
                $html .= '</div>';
                unset($oldData['status']);
            }
            if (isset($oldData['priority'])) {
                $html .= '<div class="form-group">';
                $html .= '<label class="col-xs-4 control-label">'.Translator::trans('ticket.priority').': </label>';
                $html .= '<div class="col-xs-8">'.$this->getPriorityTranslate($oldData['priority']).'</div>';
                $html .= '</div>';
                unset($oldData['priority']);
            }
            if (isset($oldData['message'])) {
                $html .= '<div class="form-group">';
                $html .= '<label class="col-xs-4 control-label">'.Translator::trans('message').': </label>';
                $html .= '<div class="col-xs-8 ltr">#'.$oldData['message']->id.'</div>';
                $html .= '</div>';
                unset($oldData['message']);
            }

            if (isset($oldData['labels'])) {
                $labels = '';
                foreach ($oldData['labels'] as $label) {
                    $labels .= $this->getLabel(new Label($label), 'ticketing');
                }

                $html .= '<div class="form-group">';
                $html .= '<label class="col-xs-4 control-label">'.t('titles.ticketing.labels').': </label>';
                $html .= '<div class="col-xs-8 ltr ticket-labels">'.$labels.'</div>';
                $html .= '</div>';
                unset($oldData['labels']);
            }

            foreach ($oldData as $field => $val) {
                $html .= '<div class="form-group">';
                $html .= '<label class="col-xs-4 control-label">'.Translator::trans("ticket.{$field}").': </label>';
                $html .= '<div class="col-xs-8">'.$val.'</div>';
                $html .= '</div>';
            }

            $panel->setHTML($html);
            $this->addPanel($panel);
        }

        $newData = $parameters['newData'] ?? [];

        if (isset($newData['labels'])) {
            $panel = new Panel('ticketing.logs.ticket.edit.added');
            $panel->icon = 'fa fa-plus';
            $panel->size = 6;
            $panel->title = t('titles.ticketing.logs.added_data');
            $html = '';

            $labels = '';
            foreach ($newData['labels'] as $label) {
                $labels .= $this->getLabel(new Label($label), 'ticketing');
            }

            $html .= '<div class="form-group">';
            $html .= '<label class="col-xs-4 control-label">'.t('titles.ticketing.labels').': </label>';
            $html .= '<div class="col-xs-8 ltr ticket-labels">'.$labels.'</div>';
            $html .= '</div>';

            $panel->setHTML($html);
            $this->addPanel($panel);
        }
    }
}
