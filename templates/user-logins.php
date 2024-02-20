<?php

/**
 * Template for the User Logins Table
 *
 *
 *
 * @version 1.0.0
 *
 * @var array $user_logins
 */
if (!defined('ABSPATH')) {
	exit;
}

?>

<div class="table table-responsive">


	<div class="table-responsive">
		<caption>
			<h4>User Logins</h4>
		</caption>
		<table class="table table-striped table-hover table-borded align-middle">
			<thead class="table-light">

				<tr>
					<th>Date</th>
					<th>IP Address</th>
					<th>Browser</th>
				</tr>

			</thead>
			<tbody class="table-group-divider">

				<?php foreach ($user_logins as $login) { ?>
					<tr class="">
						<td><?php echo esc_attr($login['date']); ?></td>
						<td><?php echo esc_attr($login['ip_address']); ?></td>
						<td><?php echo esc_attr($login['browser']); ?></td>
					</tr>
				<?php } ?>

			</tbody>
			<tfoot>

			</tfoot>
		</table>
	</div>

</div>