<table>
	<thead>
		<tr>
			<th><?php _e( '#' ); ?></th>
			<th><?php _e( 'Key' ); ?></th>
			<th><?php _e( 'Value' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php $index = 1; ?>
		<?php foreach( $rows as $key => $value ): ?>
			<tr>
				<td><?php echo $index ?></td>
				<td><?php echo $key ?></td>
				<td><?php echo $value ?></td>
			</tr>
			<?php $index++; ?>
		<?php endforeach; ?>
	</tbody>
</table>
