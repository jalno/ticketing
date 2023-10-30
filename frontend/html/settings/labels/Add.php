<?php

use packages\base\Translator;
use function packages\userpanel\url;

$this->the_header();
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-tag"></i>
	<?php echo t('titles.ticketing.labels.add'); ?>
		<div class="panel-tools">
			<a class="btn btn-xs btn-link panel-collapse collapses"></a>
		</div>
	</div>
	<div class="panel-body">
		<form id="labels-add-edit-form" action="<?php echo url('settings/ticketing/labels/add'); ?>" method="POST">
			<div class="row">
				<div class="col-sm-6">
				<?php
                $this->createField([
                    'name' => 'title',
                    'label' => t('titles.ticketing.labels.title'),
                    'required' => true,
                ]);
                $this->createField([
                    'name' => 'color',
                    'label' => t('titles.ticketing.labels.color'),
                    'type' => 'color',
                    'required' => true,
                ]);
                ?>
				</div>
				<div class="col-sm-6">
				<?php $this->createField([
                    'name' => 'description',
                    'label' => t('titles.ticketing.labels.description'),
					'type' => 'textarea',
					'rows' => 5,
                ]); ?>
				</div>
			</div>
		</form>
	</div>
	<div class="panel-footer">
		<div class="row">
			<div class="col-lg-4 col-md-5 col-sm-6 col-xs-12 col-lg-offset-8 col-md-offset-7 col-sm-offset-6">
				<div class="row">
					<div class="col-sm-6 col-xs-12">
						<button type="submit" form="labels-add-edit-form" class="btn btn-block btn-success">
							<div class="btn-icons"><i class="fa fa-check-square-o"></i></div>
						<?php echo t('submit'); ?>
						</button>
					</div>
					<div class="col-sm-6 col-xs-12">
						<a href="<?php echo url('settings/ticketing/labels'); ?>" class="btn btn-block btn-default">
							<div class="btn-icons"><i class="fa fa-angle-<?php echo Translator::getLang()->isRTL() ? 'right' : 'left'; ?>"></i></div>
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
