<?php

use packages\base\Translator;

use function packages\userpanel\url;

$this->the_header();
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-file-text-o"></i>
	<?php echo t('titles.ticketing.labels.delete'); ?>
		<div class="panel-tools">
			<a class="btn btn-xs btn-link panel-collapse collapses"></a>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-6">
			<?php
            $this->createField([
                'name' => 'title',
                'label' => t('titles.ticketing.labels.title'),
                'disabled' => true,
            ]);
$this->createField([
    'name' => 'color',
    'label' => t('titles.ticketing.labels.color'),
    'type' => 'color',
    'disabled' => true,
]);
?>
			</div>
			<div class="col-sm-6">
			<?php $this->createField([
			    'name' => 'description',
			    'label' => t('titles.ticketing.labels.description'),
			    'type' => 'textarea',
			    'rows' => 5,
			    'disabled' => true,
			]); ?>
			</div>
		</div>

		<div class="alert alert-danger alert-block">
			<h4 class="alert-heading"><?php echo t('error.warning.title'); ?></h4>
			<p>
			<?php
$ticketsCount = $this->getTicketsCount();
echo $ticketsCount ? t('ticketing.label.delete.with_tickets', ['tickets' => $ticketsCount, 'url' => url('ticketing', ['labels' => $this->label->getID()])]) : t('ticketing.label.delete');
?>
			</p>
		</div>
	</div>
	<div class="panel-footer">
		<div class="row">
			<div class="col-lg-4 col-md-5 col-sm-6 col-xs-12 col-lg-offset-8 col-md-offset-7 col-sm-offset-6">
				<div class="row">
					<div class="col-sm-6 col-xs-12">
						<form action="<?php echo url('settings/ticketing/labels/delete/'.$this->label->id); ?>" method="POST">
							<button type="submit" class="btn btn-block btn-bricky">
								<div class="btn-icons"><i class="fa fa-trash-o"></i></div>
							<?php echo t('delete'); ?>
							</button>
						</form>
					</div>
					<div class="col-sm-6 col-xs-12">
						<a href="<?php echo url('settings/ticketing/labels'); ?>" class="btn btn-block btn-default">
							<div class="btn-icons"><i class="fa fa-angle-<?php echo Translator::isRTL() ? 'right' : 'left'; ?>"></i></div>
						<?php echo t('return'); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
$this->the_footer();
