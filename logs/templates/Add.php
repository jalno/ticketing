<?php
namespace packages\ticketing\logs\templates;

use packages\base\View;
use packages\ticketing\contracts\ITemplate;
use packages\userpanel\User;
use packages\userpanel\Log;
use packages\userpanel\Logs;

class Add extends Logs
{
	public static function create(ITemplate $template, ?User $user = null): void
	{
		$log = new Log();
		$log->user = $user;
		$log->title = t('ticketing.logs.templates.add', [
			'id' => $template->id
		]);
		$log->type = self::class;
		$log->parameters = [
			'template' => $template->toArray(),
		];

		$log->save();
	}

	public function getColor(): string
	{
		return 'circle-green';
	}

	public function getIcon(): string
	{
		return 'fa fa-file-text-o';
	}

	public function buildFrontend(View $view): void
	{
	}
}
