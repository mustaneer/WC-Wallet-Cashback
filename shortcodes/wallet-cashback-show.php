<section class="feedback-form-wrapper">
	<h2 class="heading full"><?php echo __('Submit your feedback', $this->plugin_name); ?></h2>
	<form action="" method="post" class="feedback-form">
	   	<input type="hidden" name="action" value="feedback_form_data" />
	   	<input type="hidden" name="feedback_nonce" value="<?php echo wp_create_nonce("feedback_form_nonce"); ?>" />
	    <div class="form-group">
	        <div class="controls half">
	            <input type="text" id="first_name" class="float_label" name="first_name" value="<?php echo $first_name; ?>" required="required" />
	            <label for="first_name"><?php echo __('First Name', $this->plugin_name); ?></label>
	        </div>
	        <div class="controls half">
	            <input type="text" id="last_name" class="float_label" name="last_name" value="<?php echo $last_name; ?>" required="required" />
	            <label for="last_name"><?php echo __('Last Name', $this->plugin_name); ?></label>
	        </div>
	        <div class="controls full">
	            <input type="email" id="email" class="float_label" name="email" value="<?php echo $email; ?>" required="required" />
	            <label for="email"><?php echo __('Email', $this->plugin_name); ?></label>
	        </div>
	    </div>
	    <!--  Details -->
	    <div class="form-group">
	        <div class="controls full">
	            <input type="text" id="subject" class="float_label" name="subject" required="required" />
	            <label for="subject"><?php echo __('Subject', $this->plugin_name); ?></label>
	        </div>
	    </div>
	    <!--  More -->
	    <div class="form-group">
	        <div class="controls full">
	            <textarea name="message" class="float_label" id="message" required="required" /></textarea>
	            <label for="message"><?php echo __('Message', $this->plugin_name); ?></label>
	            <button class="full" type="submit">Submit</button>
	        </div>
	    </div>
	</form>
	<div class="feedback-form-notifications"></div>
</section>
