<?php
use \packages\base;
use \packages\base\translator;
use \packages\userpanel;

$this->the_header();
?>
<div class="row">
    <div class="col-md-12">
        <!-- start: BASIC TABLE PANEL -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="clip-user-6"></i>
                <span><?php echo translator::trans("ticket.edit.notice.title").' #'.$this->getTicketData()->id; ?></span>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <form action="<?php echo userpanel\url('ticketing/edit/'.$this->getTicketData()->id) ?>" method="post">
                        <div class="col-md-6">
                            <?php
						$fields = array(
							array(
								'name' => 'title',
								'label' => translator::trans("ticket.title"),
								'value' => $this->getTicketData()->title
							),
							array(
								'name' => 'priority',
								'type' => 'select',
								'label' => translator::trans("ticket.priority"),
								'options' => $this->getpriortyForSelect(),
								'value' => $this->getTicketData()->priority
							),
							array(
								'name' => 'department',
								'type' => 'select',
								'label' => translator::trans("ticket.department"),
								'options' => $this->department,
								'value' => $this->getTicketData()->department->id
								)
							);
							foreach($fields as $field){
								$this->createField($field);
							}
							?>
                        </div>
                        <div class="col-md-6">
                            <?php
							$fields = array(
								array(
									'name' => 'client',
									'label' => translator::trans("ticket.client"),
									'value' => $this->getTicketData()->client->id,
									'error' => array(
										'data_validation' => 'ticket.client.data_validation'
									)
								),
								array(
									'name' => 'status',
									'type' => 'select',
									'label' => translator::trans("ticket.status"),
									'options' => $this->getStatusForSelect(),
									'value' => $this->getTicketData()->status
									)
								);
								foreach($fields as $field){
									$this->createField($field);
								}
								?>
                        </div>
						<div class="col-md-12">
			                <hr>
			                <p>
			                    <a href="<?php echo userpanel\url('ticketing/view/'.$this->getTicketData()->id); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('return'); ?></a>
			                    <button type="submit" class="btn btn-yellow"><i class="fa fa-check-square-o"></i> <?php echo translator::trans("update") ?></button>
			                </p>
						</div>
	                </form>
                </div>
            </div>
        </div>
        <!-- end: BASIC TABLE PANEL -->
    </div>
</div>
<?php
	$this->the_footer();
