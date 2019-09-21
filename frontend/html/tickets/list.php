<?php
use packages\userpanel;
use themes\clipone\utility;
use packages\userpanel\date;
use packages\base\translator;
use packages\ticketing\ticket;
if (!$this->isTab) {
	$this->the_header();
}
$tickets = $this->getOrderedTickets();
$hasTicket = !empty($tickets);
?>
<div class="ticket-status-search">
	<div class="row">
	<?php if ($this->canAdd) { ?>
		<div class="col-sm-4 col-sm-pull-8 col-xs-12">
			<a href="<?php echo userpanel\url('ticketing/new'); ?>" class="btn btn-success pull-left">
				<div class="btn-icons"> <i class="fa fa-message"></i> </div>
				تیکت جدید
			</a>
		</div>
	<?php } ?>
		<div class="col-sm-8 col-sm-push-4 col-xs-12">
			<ul role="tablist">
				<li role="presentation" class="<?php echo $this->isActive("active") ? "active" : ""; ?>">
					<a href="<?php echo userpanel\url($this->getPath(), array("status" => implode(",", array(ticket::read, ticket::answered, ticket::unread, ticket::in_progress)))); ?>">فعال</a>
				</li>
				<li role="presentation" class="<?php echo $this->isActive("inProgress") ? "active" : ""; ?>">
					<a href="<?php echo userpanel\url($this->getPath(), array("status" => ticket::in_progress)); ?>">در حال پیگیری</a>
				</li>
				<li role="presentation" class="<?php echo $this->isActive("closed") ? "active" : ""; ?>">
					<a href="<?php echo userpanel\url($this->getPath(), array("status" => ticket::closed)); ?>">بسته شده</a>
				</li>
				<li role="presentation" class="<?php echo $this->isActive() ? "active" : ""; ?>">
					<a href="<?php echo userpanel\url($this->getPath(), array("status" => implode(",", array(ticket::unread, ticket::read, ticket::in_progress, ticket::answered, ticket::closed)))); ?>">همه</a>
				</li>
			</ul>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<div class="ticket-advanced-search">
			<form id="tickets-search" action="<?php echo userpanel\url($this->getPath()); ?>">
				<?php
				$this->createField(array(
					"name" => "word",
					"placeholder" => t("ticketing.ticket.keyword"),
					"input-group" => array(
						"left" => array(
							array(
								"type" => "button",
								"class" => "btn btn-default advanced-search",
								"text" => "جستجو پیشرفته",
								"icon" => "fa fa-search-plus",
							),
						),
					),
				));
				?>
				<div class="row more-field">
					<div class="col-sm-6 col-xs-12">
						<?php
						$this->createField(array(
							"name" => "status",
							"type" => "hidden",
						));
						$this->createField(array(
							"name" => "id",
							"type" => "number",
							"ltr" => true,
							"label" => t("ticket.id"),
						));
						$this->createField(array(
							"name" => "title",
							"label" => t("ticket.title"),
						));
						if (!$this->isTab) {
							$this->createField(array(
								"name" => "priority",
								"type" => "select",
								"label" => t("ticket.priority"),
								"options" => $this->getPriortyForSelect(),
							));
						}
						?>
					</div>
					<div class="col-sm-6 col-xs-12">
						<?php
						$this->createField(array(
							"name" => "department",
							"type" => "select",
							"label" => t("ticket.department"),
							"options" => $this->getDepartmentsForSelect()
						));
						if ($this->multiuser and !$this->isTab) {
							$this->createField(array(
								"name" => "client",
								"type" => "hidden",
							));
							$this->createField(array(
								"name" => "client_name",
								"label" => t("ticket.client"),
							));
						}
						?>
						<?php if ($this->isTab) { ?>
						<div class="row">
							<div class="col-sm-6">
								<?php
								$this->createField(array(
									"name" => "priority",
									"type" => "select",
									"label" => t("ticket.priority"),
									"options" => $this->getPriortyForSelect(),
								));
								?>
							</div>
							<div class="col-sm-6">
								<?php
								$this->createField(array(
									"type" => "select",
									"label" => t("search.comparison"),
									"name" => "comparison",
									"options" => $this->getComparisonsForSelect()
								));
								?>
							</div>
						</div>
						<?php
							} else { 
								$this->createField(array(
									"type" => "select",
									"label" => t("search.comparison"),
									"name" => "comparison",
									"options" => $this->getComparisonsForSelect()
								));
							}
						?>
					</div>
					<div class="col-xs-12">
						<button class="btn btn-default pull-left" type="submit">
							<div class="btn-icons"> <i class="fa fa-search"></i> </div>
							جستجو
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<?php if ($hasTicket) { ?>
<div class="tickets-list">
<?php
$showStatus = $this->isActive("all");
if (! $showStatus and $this->isActive("active")) {
	$lastStatus = $tickets[0]->status;
	foreach ($tickets as $ticket) {
		if ($lastStatus != $ticket->status) {
			$showStatus = true;
			break;
		}
	}
}
foreach ($tickets as $ticket) {
?>
	<div class="ticket">
		<div class="row">
			<div class="col-sm-8 col-xs-12">
			<?php $hasUnreadMessage = $ticket->hasUnreadMessage(); ?>
				<p>
				<?php
				if ($showStatus) {
					$statusClass = utility::switchcase($ticket->status, [
						"fa fa-info-circle text-primary" => ticket::unread,
						"fa fa-eye text-info" => ticket::read,
						"fa fa-check-square-o text-success" => ticket::answered,
						"fa fa-spinner fa-spin text-warning" => ticket::in_progress,
						"fa fa-window-close text-inverse" => ticket::closed
					]);
					$statusTxt = utility::switchcase($ticket->status, [
						"unread" => ticket::unread,
						"read" => ticket::read,
						"answered" => ticket::answered,
						"in_progress" => ticket::in_progress,
						"closed" => ticket::closed
					]);
				?>
					<i class="<?php echo $statusClass; ?> tooltips" title="<?php echo t($statusTxt); ?>"></i>
				<?php } ?>
					<a class="btn-link<?php echo $hasUnreadMessage > 0 ? " has-unread-message" : ""; ?>" href="<?php echo $this->canView ? userpanel\url("ticketing/view/{$ticket->id}") : "javascript:void();"; ?>">
					<?php echo $ticket->title; ?>
					</a>
				</p>
				<p>
					<span>#<?php echo $ticket->id; ?></span>
					<span><span class="tooltips" title="<?php echo date::format("Y/m/d H:i", $ticket->create_at); ?>"><?php echo date::relativeTime($ticket->create_at); ?></span></span>
				<?php if ($this->multiuser) { ?>
					<span>
						توسط
						<a class="client" href="<?php echo userpanel\url("users", array("id" => $ticket->client->id)); ?>"><?php echo $ticket->client->getFullName(); ?></a>
					</span>
				<?php } ?>
					<span>در دپارتمان <?php echo $ticket->department->title; ?></span>
				</p>
			</div>
			<div class="col-sm-4 col-xs-12 ticket-info">
				<p>
				<?php
				if ($ticket->operator_id) {
				?>
					<span>
						<a target="_blank" href="<?php echo $this->hasAccessToUsers ? userpanel\url("users", array("id" => $ticket->operator_id)) : "javascript::void()"; ?>" class="tooltips" title="<?php echo $ticket->operator->getFullName(); ?>">
							<img class="img-circle" src="<?php echo $ticket->operator->getAvatar(16, 16); ?>" alt="<?php echo $ticket->operator->getFullName(); ?>">
						</a>
					</span>
				<?php
				}
				$priorityClass = utility::switchcase($ticket->priority, array(
					"label label-warning" => ticket::instantaneous,
					"label label-primary" => ticket::important,
					"label label-info" => ticket::ordinary,
				));
				$priorityTxt = utility::switchcase($ticket->priority, array(
					"instantaneous" => ticket::instantaneous,
					"important" => ticket::important,
					"ordinary" => ticket::ordinary,
				));
				?>
					<span class="<?php echo $priorityClass; ?>"><?php echo t($priorityTxt); ?></span>
				<?php $messageCount = $ticket->getMessageCount(); ?>
					<span><?php echo $messageCount; ?> <i class="fa fa-comments-o"></i></span>
				</p>
			<?php if ($ticket->reply_at and $ticket->reply_at != $ticket->create_at) { ?>
				<p>
					<span>آخرین پاسخ <span class="tooltips" title="<?php echo date::format("Y/m/d H:i", $ticket->reply_at); ?>"><?php echo date::relativeTime($ticket->reply_at); ?></span></span>
				</p>
			<?php } ?>
			</div>
		</div>
	</div>
<?php } ?>
</div>
<div class="row">
	<div class="col-xs-10 pull-left">
		<?php $this->paginator(); ?>
	</div>
</div>
<?php
} else {
?>
	<div class="alert alert-info">
		<h4 class="alert-heading"><i class="fa fa-info-circle"></i> توجه</h4>
	<?php echo t("error.ticketing.ticket.notfound"); ?>
	</div>
<?php } ?>
<?php
if (!$this->isTab) {
	$this->the_footer();
}