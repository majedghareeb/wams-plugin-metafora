<?php if (!defined('ABSPATH')) exit; ?>


<div class="table">

	<table>
		<tr class="first">
			<td class="first b">
				<a href="<?php echo esc_url(admin_url('users.php')); ?>">
					<?php echo WAMS()->query()->count_users(); ?>
				</a>
			</td>
			<td class="t">
				<a href="<?php echo esc_url(admin_url('users.php')); ?>">
					<?php _e('Users', 'wams'); ?>
				</a>
			</td>
		</tr>

		<tr>
			<td class="first b">
				<a href="<?php echo esc_url(admin_url('users.php?wams_status=approved')); ?>">
					<?php echo WAMS()->query()->count_users_by_status('approved'); ?>
				</a>
			</td>
			<td class="t">
				<a href="<?php echo esc_url(admin_url('users.php?wams_status=approved')); ?>">
					<?php _e('Approved', 'wams'); ?>
				</a>
			</td>
		</tr>

		<tr>
			<td class="first b">
				<a href="<?php echo esc_url(admin_url('users.php?wams_status=rejected')); ?>">
					<?php echo WAMS()->query()->count_users_by_status('rejected'); ?>
				</a>
			</td>
			<td class="t">
				<a href="<?php echo esc_url(admin_url('users.php?wams_status=rejected')); ?>">
					<?php _e('Rejected', 'wams'); ?>
				</a>
			</td>
		</tr>
	</table>

</div>

<div class="table table_right">

	<table>
		<tr class="first">
			<td class="b">
				<a href="<?php echo esc_url(admin_url('users.php?wams_status=awaiting_admin_review')); ?>">
					<?php echo WAMS()->query()->count_users_by_status('awaiting_admin_review'); ?>
				</a>
			</td>
			<td class="last t">
				<a href="<?php echo esc_url(admin_url('users.php?wams_status=awaiting_admin_review')); ?>" class="warning">
					<?php _e('Pending Review', 'wams'); ?>
				</a>
			</td>
		</tr>

		<tr>
			<td class="b">
				<a href="<?php echo esc_url(admin_url('users.php?wams_status=awaiting_email_confirmation')); ?>">
					<?php echo WAMS()->query()->count_users_by_status('awaiting_email_confirmation'); ?>
				</a>
			</td>
			<td class="last t">
				<a href="<?php echo esc_url(admin_url('users.php?wams_status=awaiting_email_confirmation')); ?>" class="warning">
					<?php _e('Awaiting E-mail Confirmation', 'wams'); ?>
				</a>
			</td>
		</tr>

		<tr>
			<td class="first b">
				<a href="<?php echo esc_url(admin_url('users.php?wams_status=inactive')); ?>">
					<?php echo WAMS()->query()->count_users_by_status('inactive'); ?>
				</a>
			</td>
			<td class="t">
				<a href="<?php echo esc_url(admin_url('users.php?wams_status=inactive')); ?>">
					<?php _e('Inactive', 'wams'); ?>
				</a>
			</td>
		</tr>
	</table>

</div>
<div class="clear"></div>