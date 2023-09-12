<?php

use packages\ticketing\Template;
use function packages\userpanel\url;
use themes\clipone\Utility;

$this->the_header();

if ($this->canAdd) {
    ?>
<div class="row">
	<div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 col-lg-offset-10 col-md-offset-9 col-sm-offset-8">
		<a class="btn btn-block btn-success btn-acion" href="<?php echo url('settings/ticketing/templates/add'); ?>">
			<div class="btn-icons"><i class="fa fa-plus"></i></div>
		<?php echo t('add'); ?>
		</a>
	</div>
</div>
<?php
} ?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-file-text-o"></i>
	<?php echo t('titles.ticketing.templates'); ?>
		<div class="panel-tools">
			<a class="btn btn-xs btn-link tooltips" title="<?php echo t('search'); ?>" href="#search-templates-modal" data-toggle="modal" data-original-title=""><i class="fa fa-search"></i></a>
			<a class="btn btn-xs btn-link panel-collapse collapses"></a>
		</div>
	</div>
	<div class="panel-body">
	<?php
    $templates = $this->getDataList();
    if ($templates) {
        ?>
		<div class="table-responsive">
			<table class="table table-hover">
			<?php $hasButtons = $this->hasButtons(); ?>
				<thead>
					<tr>
						<th class="center">#</th>
						<th><?php echo t('titles.ticketing.templates.title'); ?></th>
						<th><?php echo t('titles.ticketing.templates.subject'); ?></th>
						<th class="center"><?php echo t('titles.ticketing.templates.message_type'); ?></th>
						<th class="center"><?php echo t('titles.ticketing.message_format'); ?></th>
						<th class="center"><?php echo t('titles.ticketing.templates.status'); ?></th>
					<?php if ($hasButtons) { ?>
						<th class="center"><?php echo t('titles.ticketing.actions'); ?></th>
					<?php } ?>
					</tr>
				</thead>
				<tbody>
				<?php
                foreach ($templates as $template) {
                    $this->setButtonParam('edit', 'link', url('settings/ticketing/templates/edit/'.$template->getID()));
                    $this->setButtonParam('delete', 'link', url('settings/ticketing/templates/delete/'.$template->getID()));

                    $statusClass = Utility::switchcase($template->getStatus(), [
                        'label label-success' => Template::ACTIVE,
                        'label label-inverse' => Template::DEACTIVE,
                    ]);

                    $statusTranslate = Utility::switchcase($template->getStatus(), [
                        'titles.ticketing.templates.status.active' => Template::ACTIVE,
                        'titles.ticketing.templates.status.deactive' => Template::DEACTIVE,
                    ]);

                    $messageType = Utility::switchcase($template->getMessageType(), [
                        'titles.ticketing.all' => null,
                        'titles.ticketing.templates.message_type.add' => Template::ADD,
                        'titles.ticketing.templates.message_type.reply' => Template::REPLY,
                    ]); ?>
					<tr>
						<td class="center"><?php echo $template->getID(); ?></td>
						<td><?php echo $template->getTitle(); ?></td>
						<td><?php echo $template->getSubject() ?: '-'; ?></td>
						<td class="center"><?php echo t($messageType); ?></td>
						<td class="center"><?php echo ucfirst($template->getMessageFormat()); ?></td>
						<td class="center">
							<span class="<?php echo $statusClass; ?>"><?php echo t($statusTranslate); ?></span>
						</td>
					<?php if ($hasButtons) { ?>
						<td class="center"><?php echo $this->genButtons(['edit', 'delete']); ?></td>
					<?php } ?>
					</tr>
				<?php
                } ?>
				</tbody>
			</table>
		</div>
	<?php
        $this->paginator();
    } else {
        ?>
		<div class="alert alert-block alert-warning">
			<p>
				<i class="fa fa-exclamation-triangle"></i>
			<?php echo t('warning.ticketing.empty'); ?>
			</p>
		</div>
	<?php
    } ?>
	</div>
</div>

<div class="modal fade" id="search-templates-modal" tabindex="-1" data-show="true" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title"><?php echo t('search'); ?></h4>
	</div>
	<div class="modal-body">
		<form id="search-templates-form" class="form-horizontal" action="<?php echo url('settings/ticketing/templates'); ?>" method="GET">
		<?php
        $this->setHorizontalForm('sm-3', 'sm-9');

        $feilds = [
            [
                'name' => 'id',
                'type' => 'number',
                'label' => t('ticket.id'),
            ],
            [
                'name' => 'title',
                'label' => t('titles.ticketing.templates.title'),
            ],
            [
                'name' => 'subject',
                'label' => t('titles.ticketing.templates.subject'),
            ],
            [
                'name' => 'message_type',
                'label' => t('titles.ticketing.templates.message_type'),
                'type' => 'select',
                'options' => $this->getMessageTypesForSelect(),
            ],
            [
                'name' => 'message_format',
                'label' => t('titles.ticketing.message_format'),
                'type' => 'select',
                'options' => $this->getMessageFormatsForSelect(true),
            ],
            [
                'name' => 'status',
                'label' => t('titles.ticketing.templates.status'),
                'type' => 'select',
                'options' => $this->getStatusesForSelect(),
            ],
            [
                'name' => 'department',
                'label' => t('ticket.department'),
                'type' => 'select',
                'options' => $this->getDepartmentsForSelect(),
            ],
            [
                'name' => 'word',
                'label' => t('ticketing.ticket.keyword'),
            ],
            [
                'type' => 'select',
                'label' => t('search.comparison'),
                'name' => 'comparison',
                'options' => $this->getComparisonsForSelect(),
            ],
        ];
        foreach ($feilds as $input) {
            $this->createField($input);
        }
        ?>
		</form>
	</div>
	<div class="modal-footer">
		<button type="submit" form="search-templates-form" class="btn btn-success"><?php echo t('search'); ?></button>
		<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo t('cancel'); ?></button>
	</div>
</div>
<?php
$this->the_footer();
