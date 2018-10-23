<?php
use \packages\base\translator;
use \packages\userpanel;
use \packages\ticketing\ticket_message;
use \packages\ticketing\authentication;
$this->the_header();
?>
<div class="row">
	<div class="col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-plus"></i>
				<span><?php echo translator::trans("newticket")?></span>
			</div>
			<div class="panel-body">
				<form id="ticket-add" action="<?php echo userpanel\url('ticketing/new') ?>" method="post"  enctype="multipart/form-data" spellcheck="false">
					<div class="row">
						<div class="col-sm-6">
						<?php
						if($this->multiuser){
							$this->createField(array(
								'name' => 'client',
								'type' => 'hidden'
							));
							$this->createField(array(
								'name' => 'client_name',
								'label' => translator::trans("newticket.client"),
								'error' => array(
									'data_validation' => 'newticket.client.data_validation'
								)
							));
						}
						$fields = array(
							array(
								'name' => 'title',
								'label' => translator::trans("newticket.title"),
							),
							array(
								'name' => 'product',
								'type' => 'select',
								'label' => translator::trans("newticket.typeservice"),
								'options' => $this->products(),
								'value' => ''
							)
						);
						foreach($fields as $field){
							$this->createField($field);
						}
						?>
						</div>
						<div class="col-sm-6">
						<?php
						$fields = array(
							array(
								'name' => 'priority',
								'type' => 'select',
								'label' => translator::trans("newticket.priority"),
								'options' => $this->getpriortyForSelect()
							),
							array(
								'name' => 'department',
								'type' => 'select',
								'label' => translator::trans("newticket.department"),
								'options' => $this->getDepartmentsForSelect()
							),
							array(
								'name' => 'service',
								'type' => 'select',
								'label' => translator::trans("newticket.service"),
								'options' => array()
							)
						);
						foreach($fields as $field){
							$this->createField($field);
						}
						?>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<?php
							$this->createField(array(
								'name' => 'text',
								'type' => 'textarea',
								'rows' => 4
							));
							?>
							<hr>
						</div>
					</div>
					<div class="row">
						<?php
						$editor = authentication::getUser()->getOption('ticketing_editor');
						if (!$editor or $editor == ticket_message::html) {
						?>
						<div class="col-sm-7">
							<p><?php echo translator::trans('markdown.description', ['settings.url'=>userpanel\url('profile/settings')]); ?></p>
						</div>
						<?php } ?>
						<div class="col-sm-5 text-left <?php echo $editor != ticket_message::html ? 'col-sm-offset-7' : ''; ?>">
							<div class="btn-group btn-group-lg" role="group">
								<span class="btn btn-file2">
									<i class="fa fa-upload"></i> <?php echo translator::trans("upload") ?>
									<input type="file" name="file[]" multiple="">
								</span>
								<button class="btn btn-teal" type="submit"><i class="fa fa-paper-plane"></i><?php echo translator::trans("send"); ?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
echo $this->generateShortcuts();
echo $this->generateRows();
$this->the_footer();
