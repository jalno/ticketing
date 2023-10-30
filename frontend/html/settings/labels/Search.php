<?php

use packages\ticketing\Label;
use function packages\userpanel\url;
use themes\clipone\Utility;

$this->the_header();

if ($this->canAdd) {
    ?>
<div class="row">
	<div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 col-lg-offset-10 col-md-offset-9 col-sm-offset-8">
		<a class="btn btn-block btn-success btn-acion" href="<?php echo url('settings/ticketing/labels/add'); ?>">
			<div class="btn-icons"><i class="fa fa-plus"></i></div>
		<?php echo t('add'); ?>
		</a>
	</div>
</div>
<?php
} ?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-tag"></i>
	<?php echo t('titles.ticketing.labels'); ?>
		<div class="panel-tools">
			<a class="btn btn-xs btn-link tooltips" title="<?php echo t('search'); ?>" href="#search-labels-modal" data-toggle="modal" data-original-title=""><i class="fa fa-search"></i></a>
			<a class="btn btn-xs btn-link panel-collapse collapses"></a>
		</div>
	</div>
	<div class="panel-body">
	<?php
    $labels = $this->getDataList();
    if ($labels) {
        ?>
		<div class="table-responsive">
			<table class="table table-hover">
			<?php $hasButtons = $this->hasButtons(); ?>
				<thead>
					<tr>
						<th class="center">#</th>
						<th><?php echo t('titles.ticketing.labels.title'); ?></th>
						<th class="center"><?php echo t('titles.ticketing.labels.status'); ?></th>
					<?php if ($hasButtons) { ?>
						<th class="center"><?php echo t('titles.ticketing.actions'); ?></th>
					<?php } ?>
					</tr>
				</thead>
				<tbody>
				<?php
                foreach ($labels as $label) {
                    $this->setButtonParam('edit', 'link', url('settings/ticketing/labels/edit/'.$label->getID()));
                    $this->setButtonParam('delete', 'link', url('settings/ticketing/labels/delete/'.$label->getID()));

                    $statusClass = Utility::switchcase($label->getStatus(), [
                        'label label-success' => Label::ACTIVE,
                        'label label-inverse' => Label::DEACTIVE,
                    ]);

                    $statusTranslate = Utility::switchcase($label->getStatus(), [
                        'titles.ticketing.labels.status.active' => Label::ACTIVE,
                        'titles.ticketing.labels.status.deactive' => Label::DEACTIVE,
                    ]);
                    ?>
					<tr>
						<td class="center"><?php echo $label->getID(); ?></td>
						<td><?php echo $this->getLabel($label->getTitle(), $label->getColor()); ?></td>
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

<div class="modal fade" id="search-labels-modal" tabindex="-1" data-show="true" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title"><?php echo t('search'); ?></h4>
	</div>
	<div class="modal-body">
		<form id="search-labels-form" class="form-horizontal" action="<?php echo url('settings/ticketing/labels'); ?>" method="GET">
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
                'label' => t('titles.ticketing.labels.title'),
            ],
            [
                'name' => 'status',
                'label' => t('titles.ticketing.labels.status'),
                'type' => 'select',
                'options' => $this->getStatusForSelect(true),
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
		<button type="submit" form="search-labels-form" class="btn btn-success"><?php echo t('search'); ?></button>
		<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo t('cancel'); ?></button>
	</div>
</div>
<?php
$this->the_footer();
