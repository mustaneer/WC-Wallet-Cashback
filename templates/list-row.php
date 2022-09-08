<li class="table-row" data-fid="<?php echo $result->fid; ?>">
	<div class="col col-1"><strong><?php echo __('First Name', $this->plugin_name); ?></strong></label><?php echo $result->firstname; ?></div>
	<div class="col col-2"><strong><?php echo __('Last Name', $this->plugin_name); ?></strong><?php echo $result->lastname; ?></div>
	<div class="col col-3"><strong><?php echo __('Email', $this->plugin_name); ?></strong><?php echo $result->email; ?></div>
	<div class="col col-4"><strong><?php echo __('Subject', $this->plugin_name); ?></strong><?php echo $result->subject; ?></div>
	<div class="col col-full col-message"><strong><?php echo __('Message', $this->plugin_name); ?></strong><span class="fid-message"></span></div>
	<div class="feedback-loader"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/images/loader.gif'; ?>" alt="feedback-loader" /></div>
	<span class="feedback-close">x</span>
</li>