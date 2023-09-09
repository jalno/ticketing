<?php

use packages\base\Translator;
use packages\ticketing\Template;
use function packages\userpanel\url;

$this->the_header();
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-file-text-o"></i>
	<?php echo t('titles.ticketing.templates.edit'); ?>
		<div class="panel-tools">
			<a class="btn btn-xs btn-link tooltips" title="<?php echo t('titles.ticketing.tutorial'); ?>" href="#tutorial-templates-modal" data-toggle="modal"><i class="fa fa-info-circle text-danger"></i></a>
			<a class="btn btn-xs btn-link panel-collapse collapses"></a>
		</div>
	</div>
	<div class="panel-body">
		<form id="templates-add-edit-form" action="<?php echo url('settings/ticketing/templates/edit/'.$this->template->id); ?>" method="POST">
			<div class="row">
				<div class="col-sm-6">
				<?php
                $this->createField([
                    'name' => 'title',
                    'label' => t('titles.ticketing.templates.title'),
                    'required' => true,
                ]);
                $this->createField([
                    'name' => 'subject',
                    'label' => t('titles.ticketing.templates.subject'),
                    'disabled' => Template::REPLY == $this->template->getMessageType(),
                ]);
                $this->createField([
                    'name' => 'message_format',
                    'label' => t('titles.ticketing.message_format'),
                    'type' => 'select',
                    'options' => $this->getMessageFormatsForSelect(),
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
                ]);
                $this->createField([
                    'name' => 'department',
                    'label' => t('ticket.department'),
                    'type' => 'select',
                    'options' => $this->getDepartmentsForSelect(),
                ]);
                ?>
				</div>
			</div>
		<?php $this->loadContentEditor(); ?>
		</form>
	</div>
	<div class="panel-footer">
		<div class="row">
			<div class="col-lg-4 col-md-5 col-sm-6 col-xs-12 col-lg-offset-8 col-md-offset-7 col-sm-offset-6">
				<div class="row">
					<div class="col-sm-6 col-xs-12">
						<button type="submit" form="templates-add-edit-form" class="btn btn-block btn-teal">
							<div class="btn-icons"><i class="fa fa-check-square-o"></i></div>
						<?php echo t('update'); ?>
						</button>
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
