 <!DOCTYPE html>
<html>
	<head>
		<title><?php echo __( 'Sunny Landing Pages Log' ) . ' ' . $current_date; ?></title>
		<style>
			body
			{
				background: #EFEFEF;
				font-family: "Courier New", "Courier", "Lucida Sans Typewriter", "Lucida Typewriter", "monospace";
				font-size: 12px;
			}

			h2
			{
				font-size: 28px;
				padding: 35px 0px;
			}

			table
			{
				border-collapse: collapse;
				width: 100%;
			}

			table,
			th,
			td
			{
   				border: 1px solid #CECECE;
   				cursor: pointer;
   			}

   			td,
   			th
   			{
				padding: 15px;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
				max-width: 0;
   			}

   			td:hover {
				text-overflow: initial;
				white-space: pre-wrap;
				word-wrap: break-word;
				overflow: auto;
   			}

   			th
   			{
   				background: #7F7F7F;
   				font-weight: bold;
   				color: #EDEDED;
   			}

   			tr:nth-child(even)
			{
				background: #F7F7F7;
			}

			tr:hover
			{
				background: #FFF;
			}
		</style>
	</head>
<body>
	<?php if( is_array( $rows ) && !empty( $rows ) ): ?>
	<h2><?php _e( 'Log entries' ); ?></h2>
	<table>
		<thead>
			<tr>
				<th width="2%"><?php _e( '#' ); ?></th>
				<th width="10%"><?php _e( 'Date' ); ?></th>
				<th width="10%"><?php _e( 'Name' ); ?></th>
				<th><?php _e( 'Text' ); ?></th>
				<th width="15%"><?php _e( 'Caller' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $rows as $index => $row ): ?>
				<tr>
					<td><?php echo $index+1 ?></td>
					<td><?php echo $row->time ?></td>
					<td><?php echo $row->name ?></td>
					<td><?php echo esc_html( $row->text ); ?></td>
					<td><?php echo $row->caller ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
		<p><?php _e( 'Log is empty.' ); ?></p>
	<?php endif; ?>
	<h2><?php _e( 'Installed plugins' ); ?></h2>
	<?php echo $plugins_html; ?>
	<h2><?php _e( 'WordPress options' ); ?></h2>
	<?php echo $options_html; ?>
	<h2><?php _e( 'PHP Info' ); ?></h2>
	<?php echo $phpinfo_html; ?>
	<h2><?php _e( 'WP Options JSON' ); ?></h2>
	<?php echo $wp_options_html; ?>
</body>
</html>
