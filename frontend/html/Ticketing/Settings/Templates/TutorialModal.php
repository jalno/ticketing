<?php
use packages\base\Frontend\Theme;

?>

<div class="modal modal-lg fade" id="tutorial-templates-modal" tabindex="-1" data-show="true" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title"><?php echo t('titles.ticketing.tutorial'); ?></h4>
	</div>
	<div class="modal-body">
		<div class="list-group">
			<div class="list-group-item">
				<h4 class="list-group-item-heading"><?php echo t('titkes.ticketing.form_fields'); ?></h4>
				<div class="list-group-item-text">
					<div class="list-group">
						<div class="list-group-item">
							<h5 class="list-group-item-heading"><?php echo t('titles.ticketing.templates.title'); ?></h5>
							<p class="list-group-item-text"><?php echo t('ticketing.templates.title'); ?></p>
						</div>
						<div class="list-group-item">
							<h5 class="list-group-item-heading"><?php echo t('titles.ticketing.templates.subject'); ?></h5>
							<p class="list-group-item-text"><?php echo t('ticketing.templates.subject'); ?></p>
						</div>
						<div class="list-group-item">
							<h5 class="list-group-item-heading"><?php echo t('titles.ticketing.templates.message_type'); ?></h5>
							<p class="list-group-item-text"><?php echo t('ticketing.templates.message_type'); ?></p>
						</div>
						<div class="list-group-item">
							<h5 class="list-group-item-heading"><?php echo t('ticket.department'); ?></h5>
							<p class="list-group-item-text"><?php echo t('ticketing.templates.department'); ?></p>
						</div>
						<div class="list-group-item">
							<h5 class="list-group-item-heading"><?php echo t('titles.ticketing.message_format'); ?></h5>
							<div class="list-group-item-text">
								<p><?php echo t('ticketing.templates.message_format'); ?></p>
								<div class="row">
									<div class="col-sm-6 col-xs-12 gallery">
										<a href="<?php echo Theme::url('assets/images/templates/tutorial-markdown-samples-1.png'); ?>" data-sub-html="<?php echo t('ticketing.templates.message_format.markdown'); ?>">
											<img class="img-responsive" src="<?php echo Theme::url('assets/images/templates/tutorial-markdown-samples-1.png'); ?>">
										</a>
									</div>
									<div class="col-sm-6 col-xs-12 gallery">
										<a href="<?php echo Theme::url('assets/images/templates/tutorial-markdown-samples-2.png'); ?>" data-sub-html="<?php echo t('ticketing.templates.preview'); ?>">
											<img class="img-responsive" src="<?php echo Theme::url('assets/images/templates/tutorial-markdown-samples-2.png'); ?>">
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="list-group-item">
							<h5 class="list-group-item-heading"><?php echo t('titles.ticketing.templates.content'); ?></h5>
							<p class="list-group-item-text"><?php echo t('ticketing.templates.content'); ?></p>
						</div>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<h4 class="list-group-item-heading"><?php echo t('titles.ticketing.templates.variables'); ?></h4>
				<div class="list-group-item-text">
					<p><?php echo t('ticketing.templates.variables', [
					    'content' => t('titles.ticketing.templates.content'),
					    'subject' => t('titles.ticketing.templates.subject'),
					]); ?></p>
					<div class="list-group">
						<div class="list-group-item">
							<h5 class="list-group-item-heading"><?php echo t('titles.ticketing.templates.variables.add'); ?></h5>
							<p class="list-group-item-text"><?php echo t('ticketing.templates.variables.add', [
							    'content' => t('titles.ticketing.templates.content'),
							    'subject' => t('titles.ticketing.templates.subject'),
							    'variable_name' => t('titles.ticketing.templates.variable'),
							]); ?></p>
						</div>
						<div class="list-group-item">
							<h5 class="list-group-item-heading"><?php echo t('titles.ticketing.templates.variables.pre_defined'); ?></h5>
							<div class="list-group-item-text">
								<p><?php echo t('ticketing.templates.variables.pre_defined'); ?></p>
								<div class="table=responsive">
									<table class="table table-strip">
										<thead>
											<tr>
												<th class="center"><?php echo t('titles.ticketing.templates.tutorial.variable.name'); ?></th>
												<th><?php echo t('titles.ticketing.templates.tutorial.variable'); ?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="center"><code data-clipboard-text="{{user_name}}">{{user_name}}</code></td>
												<td><?php echo t('ticketing.templates.variables.pre_defined.user_name'); ?></td>
											</tr>
											<tr>
												<td class="center"><code data-clipboard-text="{{user_lastname}}">{{user_lastname}}</code></td>
												<td><?php echo t('ticketing.templates.variables.pre_defined.user_lastname'); ?></td>
											</tr>
											<tr>
												<td class="center"><code data-clipboard-text="{{user_full_name}}">{{user_full_name}}</code></td>
												<td><?php echo t('ticketing.templates.variables.pre_defined.user_full_name'); ?></td>
											</tr>
											<tr>
												<td class="center"><code data-clipboard-text="{{user_email}}">{{user_email}}</code></td>
												<td><?php echo t('ticketing.templates.variables.pre_defined.user_email'); ?></td>
											</tr>
											<tr>
												<td class="center"><code data-clipboard-text="{{user_cellphone}}">{{user_cellphone}}</code></td>
												<td><?php echo t('ticketing.templates.variables.pre_defined.user_cellphone'); ?></td>
											</tr>
										</tbody>
									</table>
								</div>
								<p class="help-block"><?php echo t('ticketing.templates.variables.pre_defined.copy'); ?></p>
							</div>
						</div>
						<div class="list-group-item">
							<h5 class="list-group-item-heading"><?php echo t('titles.ticketing.samples'); ?></h5>
							<div class="list-group-item-text gallery tutorial-samples">
								<a href="<?php echo Theme::url('assets/images/templates/tutorial-samples-1.png'); ?>" data-sub-html="<?php echo t('titiles.ticketing.templates.tutorial.sample_1'); ?>">
									<img class="img-responsive" src="<?php echo Theme::url('assets/images/templates/tutorial-samples-1.png'); ?>">
								</a>
								<a href="<?php echo Theme::url('assets/images/templates/tutorial-samples-2.png'); ?>" data-sub-html="<?php echo t('titiles.ticketing.templates.tutorial.sample_2'); ?>">
									<img class="img-responsive" src="<?php echo Theme::url('assets/images/templates/tutorial-samples-2.png'); ?>">
								</a>
								<a href="<?php echo Theme::url('assets/images/templates/tutorial-samples-3.png'); ?>" data-sub-html="<?php echo t('titiles.ticketing.templates.tutorial.sample_3'); ?>">
									<img class="img-responsive" src="<?php echo Theme::url('assets/images/templates/tutorial-samples-3.png'); ?>">
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>		
	</div>
</div>