<?php

use packages\base\Translator;
use function packages\userpanel\url;

$this->the_header();
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-file-text-o"></i>
	<?php echo t('titles.ticketing.templates.delete'); ?>
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
                'label' => t('titles.ticketing.templates.title'),
                'disabled' => true,
            ]);
            $this->createField([
                'name' => 'subject',
                'label' => t('titles.ticketing.templates.subject'),
                'disabled' => true,
            ]);
            $this->createField([
                'name' => 'message_format',
                'label' => t('titles.ticketing.message_format'),
                'type' => 'select',
                'options' => $this->getMessageFormatsForSelect(),
                'disabled' => true,
            ]);
            ?>
			</div>
			<div class="col-sm-6">
			<?php
            $this->createField([
                'name' => 'message_type',
                'label' => t('titles.ticketing.templates.message_type'),
                'type' => 'select',
                'options' => $this->getMessageTypesForSelect(),
                'disabled' => true,
            ]);
            $this->createField([
                'name' => 'department',
                'label' => t('ticket.department'),
                'type' => 'select',
                'options' => [
                    [
                        'title' => $this->template->getDepartment() ? $this->template->getDepartment()->title : t('ticketing.all'),
                        'value' => '',
                    ],
                ],
                'disabled' => true,
            ]);
            ?>
			</div>
		</div>
	<?php $this->createField([
        'type' => 'textarea',
        'label' => t('titles.ticketing.templates.content'),
        'value' => $this->template->getContent(),
        'disabled' => true,
    ]); ?>

		<div class="alert alert-danger alert-block">
			<h4 class="alert-heading"><?php echo t('error.warning.title'); ?></h4>
			<p><?php echo t('ticketing.template.delete'); ?></p>
		</div>
	</div>
	<div class="panel-footer">
		<div class="row">
			<div class="col-lg-4 col-md-5 col-sm-6 col-xs-12 col-lg-offset-8 col-md-offset-7 col-sm-offset-6">
				<div class="row">
					<div class="col-sm-6 col-xs-12">
						<form action="<?php echo url('settings/ticketing/templates/delete/'.$this->template->id); ?>" method="POST">
							<button type="submit" class="btn btn-block btn-bricky">
								<div class="btn-icons"><i class="fa fa-trash-o"></i></div>
							<?php echo t('delete'); ?>
							</button>
						</form>
					</div>
					<div class="col-sm-6 col-xs-12">
						<a href="<?php echo url('settings/ticketing/templates'); ?>" class="btn btn-block btn-default">
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
$this->loadTutorialModel();
$this->the_footer();
