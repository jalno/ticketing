<?php
use \packages\base\translator;
use \packages\userpanel;
use packages\ticketing\{Ticket, ticket_message};
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
						$fields = array(
							array(
								'name' => 'title',
								'label' => translator::trans("newticket.title"),
								'required' => true,
							),
							array(
								'name' => 'department',
								'type' => 'select',
								'label' => translator::trans("newticket.department"),
								'options' => $this->getDepartmentsForSelect(),
								'required' => true,
							),
							array(
								'name' => 'product',
								'type' => 'select',
								'label' => translator::trans("newticket.typeservice"),
								'options' => array(
									array(
										'title' => t('none'),
										'value' => '',
									)
								),
							),
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
									'options' => $this->getpriortyForSelect(),
									'required' => true,
								),
								array(
									'name' => 'service',
									'type' => 'select',
									'label' => translator::trans("newticket.service"),
									'options' => array()
								),
							);
							if ($this->multiuser) {
								array_unshift($fields, array(
									'name' => 'client',
									'type' => 'hidden'
								),
								array(
									'name' => 'client_name',
									'label' => t('newticket.client'),
									'error' => array(
										'data_validation' => 'newticket.client.data_validation'
									),
								));
							}
							if ($this->canEnableDisableNotification) {
								$fields[] = array(
									'name' => 'send_notification',
									'type' => 'hidden',
								);
							}
							foreach ($fields as $field) {
								$this->createField($field);
							}
							?>
						</div>
					</div>
				<?php $this->createField(array(
					'name' => 'text',
					'label' => t('newticket.text'),
					'type' => 'textarea',
					'rows' => 4,
					'required' => true,
				)); ?>

					<hr>
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
								<?php if ($this->canEnableDisableNotification) { ?>
								<div class="btn-group btn-group-lg btn-group-notification-behavior" role="group">
									<button type="button" class="btn btn-teal dropdown-toggle btn-select-notification-behavior" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="fa fa-caret-down" aria-hidden="true"></i>
									</button>
									<button type="submit" class="btn btn-teal btn-send">
										<div class="btn-icons"><i class="fa fa-<?php echo ($this->sendNotification ? "bell" : "bell-slash") ?>" aria-hidden="true"></i></div>
									<?php echo t("send"); ?>
									</button>
									<ul class="dropdown-menu select-notification-behavior">
										<li>
											<a class="notification-behavior with-notification">
												<div class="btn-icons"><i class="fa fa-bell" aria-hidden="true"></i></div>
											<?php echo t("ticketing.send.with_notification"); ?>
											</a>
										</li>
										<li>
											<a class="notification-behavior without-notification">
												<div class="btn-icons"><i class="fa fa-bell-slash-o" aria-hidden="true"></i></div>
												<?php echo t("ticketing.send.without_notification"); ?>
											</a>
										</li>
									</ul>
								</div>
								<?php } else { ?>
								<button class="btn btn-teal" type="submit">
									<div class="btn-icons"><i class="fa fa-paper-plane"></i></div>
								<?php echo t("send"); ?>
								</button>
								<?php } ?>
								<span class="btn btn-file2">
									<div class="btn-icons"><i class="fa fa-upload"></i></div>
								<?php echo translator::trans("upload") ?>
									<input type="file" name="file[]" multiple="">
								</span>
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
