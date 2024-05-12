<?php
namespace packages\ticketing\Logs\Templates;

use packages\base\View;
use packages\ticketing\Contracts\ITemplate;
use packages\userpanel\User;
use packages\userpanel\Log;
use packages\userpanel\Logs;

class Delete extends Logs
{
	public static function create(ITemplate $template, ?User $user = null): void
	{
		$log = new Log();
		$log->user = $user;
		$log->title = t('ticketing.logs.templates.delete', [
			'id' => $template->id
		]);
		$log->type = self::class;
		$log->parameters = $template->toArray();

		$log->save();
	}

	public function getColor(): string
	{
		return 'circle-bricky';
	}

	public function getIcon(): string
	{
		return 'fa fa-file-text-o';
	}

	public function buildFrontend(View $view): void
	{
	}
}
