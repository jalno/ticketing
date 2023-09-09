<div class="tabbable" id="ticketing-editor">
	<ul class="nav nav-tabs tab-bricky">
		<li class="active">
			<a href="#editor-tab" data-toggle="tab"><?php echo t('titles.ticketing.editor'); ?></a>
		</li>
		<li>
			<a id="preview-ticket-btn" href="#preview-tab" data-toggle="tab"><?php echo t('titles.ticketing.preview'); ?></a>
		</li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane in active" id="editor-tab">
		<?php $this->createField([
			'name' => 'content',
			'type' => 'textarea',
			'rows' => 4,
			'required' => true,
			'disabled' => $this->getData('ticketing_editor_disabled'),
			'data' => [
				'message_format' => $this->getDataForm('message_format'),
			]
		]); ?>
		</div>
		<div class="tab-pane" id="preview-tab">
			<div class="alert alert-block alert-loading alert-info">
				<p>
					<i class="fa fa-spin fa-spiner"></i>
				<?php echo t('title.ticketing.loading'); ?>
				</p>
			</div>

			<div class="ticket-preview-container"></div>
		</div>
	</div>
</div>