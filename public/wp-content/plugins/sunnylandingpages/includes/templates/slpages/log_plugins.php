<table>
	<thead>
		<tr>
			<th><?php _e( '#' ); ?></th>
			<th><?php _e( 'Name' ); ?></th>
			<th><?php _e( 'Version' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php $index = 1; ?>
		<?php foreach( $rows as $key => $row ): ?>
			<tr>
				<td><?php echo $index ?></td>
				<td><?php echo $row[ 'Name' ] ?></td>
				<td><?php echo $row[ 'Version' ] ?></td>
			</tr>
			<?php $index++; ?>
		<?php endforeach; ?>
	</tbody>
</table>
