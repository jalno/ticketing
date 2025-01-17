<?php
namespace packages\ticketing\logs\templates;

use packages\base\View;
use packages\ticketing\contracts\ITemplate;
use packages\userpanel\User;
use packages\userpanel\Log;
use packages\userpanel\Logs;

class Edit extends Logs
{
	public static function create(ITemplate $template, ITemplate $origin, ?User $user = null): void
	{
		$changes = $template->getChanges($origin);
		
		if (!isset($changes['new']) or empty($changes['new'])) {
			return;
		}

		$log = new Log();
		$log->user = $user;
		$log->title = t('ticketing.logs.templates.edit', [
			'id' => $template->id
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
		return 'fa fa-file-text-o';
	}

	public function buildFrontend(View $view): void
	{
	}
}
