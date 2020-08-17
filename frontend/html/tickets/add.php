<?php
use packages\base\{json, Translator};
use packages\userpanel;
use packages\ticketing\{Ticket, ticket_message};
use packages\ticketing\Authentication;

$this->the_header();

?>
<form id="ticket-add" action="<?php echo userpanel\url('ticketing/new') ?>" method="post"  enctype="multipart/form-data" spellcheck="false">
	<div class="row">
	<?php if ($this->isSelectMultiUser or $this->canSpecifyMultiUser) { ?>
		<div class="multiuser-panel-container col-xs-12 col-sm-4 col-sm-push-8 <?php echo (!$this->hasPredefinedClients ? "display-none" : ""); ?>">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-users"></i>
					<span><?php echo t("ticketing.ticket.add.user.select_multi_user"); ?></span>
				</div>
				<div class="panel-body">
				<?php
				$this->createField(array(
					'name' => 'multiuser_mode',
					'type' => 'hidden',
				));
				$this->createField(array(
					'name' => 'clients_name',
					'label' => t("ticketing.ticket.add.user.select_multi_user.search"),
					'placeholder' => t("ticketing.ticket.add.user.select_multi_user.search.placeholder"),
				));
				?>
					<div class="multiuser-title"><?php echo t("ticketing.ticket.add.user.select_multi_user.search.added_users"); ?></div>
					<div class="multiuser-users">
						<table class="table table-striped table-bordered" data-items="<?php echo htmlentities(json\encode($this->getClientsToArray())); ?>">
							<tbody></tbody>
						</table>
					</div>

				</div>
			</div>
		</div>
	<?php } ?>

		<div class="new-ticket-panel-container col-xs-12 <?php echo ($this->isSelectMultiUser ? "col-sm-8 col-sm-pull-4" : "col-sm-12"); ?>">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-plus"></i>
					<span><?php echo translator::trans("newticket")?></span>
				</div>
				<div class="panel-body">
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
							if ($this->canSpecifyUser) {
								$client_name = array(
									'name' => 'client_name',
									'label' => t('newticket.client'),
								);
								if ($this->canSpecifyMultiUser) {
									$client_name['input-group'] = array(
										'right' => array(
											array(
												'type' => 'button',
												'text' => '<i class="fa fa-users" aria-hidden="true"></i> ' . t('ticketing.ticket.add.user.select_multi_user'),
												'class' => 'btn btn-default btn-multiuser',
												'data' => array(
													'has-clients' => $this->hasPredefinedClients,
												),
											),
										),
									);
								}
								array_unshift($fields, array(
									'name' => 'client',
									'type' => 'hidden',
									'data' => array(
										'user' => htmlentities(json\encode($this->getClient() ? $this->getClient()->toArray() : []))
									),
								), $client_name);
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
					<?php
					$this->createField(array(
						'name' => 'text',
						'label' => t('newticket.text'),
						'type' => 'textarea',
						'rows' => 4,
						'required' => true,
					));
					?>
					<hr>
					<div class="row">
						<?php
						$editor = authentication::getUser()->getOption('ticketing_editor');
						if (!$editor or $editor == ticket_message::html) {
						?>
						<div class="col-md-5 col-sm-12">
							<p><?php echo translator::trans('markdown.description', ['settings.url'=>userpanel\url('profile/settings')]); ?></p>
						</div>
						<?php } ?>
						<div class="col-md-7 col-sm-12 text-left <?php echo $editor != ticket_message::html ? 'col-sm-offset-7' : ''; ?>">
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
				</div>
			</div>
		</div>
	</div>
</form>
<?php
echo $this->generateShortcuts();
echo $this->generateRows();
$this->the_footer();
