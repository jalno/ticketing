<?php

namespace packages\ticketing\Logs\Labels;

use packages\base\View;
use packages\ticketing\Contracts\ILabel;
use packages\ticketing\Label;
use packages\userpanel\Log;
use packages\userpanel\Logs;
use packages\userpanel\User;
use themes\clipone\Utility;

class Edit extends Logs
{
    public static function create(ILabel $label, ILabel $origin, ?User $user = null): void
    {
        $changes = $label->getChanges($origin);

        if (!isset($changes['new']) or empty($changes['new'])) {
            return;
        }

        $log = new Log();
        $log->user = $user;
        $log->title = t('ticketing.logs.labels.edit', [
            'id' => $label->id,
        ]);
        $log->type = self::class;
        $log->parameters = $changes;

        $log->save();
    }

    public function getColor(): string
    {
        return 'circle-teal';
    }

    public function getIcon(): string
    {
        return 'fa fa-tag';
    }

    public function buildFrontend(View $view): void
    {
        $parameters = $this->log->parameters;

        foreach (['old', 'new'] as $item) {
            if (isset($parameters[$item])) {
                $panel = new Logs\Panel('ticketing.logs.labels.'.$item);
                $panel->icon = 'new' == $item ? 'fa fa-plus' : 'fa fa-edit';
                $panel->size = 6;
                $panel->title = t('titles.ticketing.logs.labels.'.$item);
                $html = '';

                foreach (['title', 'description'] as $field) {
                    if (!isset($parameters[$item][$field])) {
                        continue;
                    }

                    $html .= '<div class="form-group">';
                    $html .= '<label class="col-xs-4 control-label">'.t('titles.ticketing.labels.'.$field).': </label>';
                    $html .= '<div class="col-xs-8">'.($parameters[$item][$field] ?: '-').'</div>';
                    $html .= '</div>';
                }

                if (isset($parameters[$item]['color'])) {
                    $html .= '<div class="form-group">';
                    $html .= '<label class="col-xs-4 control-label">'.t('titles.ticketing.labels.color').': </label>';
                    $html .= '<div class="col-xs-8 ltr"><span class="badge" style="background-color: '.$parameters[$item]['color'].'">‌</span> '.$parameters[$item]['color'].'</div>';
                    $html .= '</div>';
                }

                if (isset($parameters[$item]['status'])) {
                    $statusClass = Utility::switchcase($parameters[$item]['status'], [
                        'label label-success' => Label::ACTIVE,
                        'label label-inverse' => Label::DEACTIVE,
                    ]);

                    $statusTranslate = Utility::switchcase($parameters[$item]['status'], [
                        'titles.ticketing.labels.status.active' => Label::ACTIVE,
                        'titles.ticketing.labels.status.deactive' => Label::DEACTIVE,
                    ]);

                    $html .= '<div class="form-group">';
                    $html .= '<label class="col-xs-4 control-label">'.t('titles.ticketing.labels.status').': </label>';
                    $html .= '<div class="col-xs-8"><span class="'.$statusClass.'">'.t($statusTranslate).'</span></div>';
                    $html .= '</div>';
                }

                $panel->setHTML($html);
                $this->addPanel($panel);
            }
        }
    }
}
