<!DOCTYPE html>
<html>
<head>
	<title><?php echo __( 'Sunny Landing Pages Error' ) . ( ( $error_code === null ) ? '' : ' ' . $error_code ); ?></title>
	<style type="text/css">
		.container
		{
			width: 400px;
			margin: 20% auto 0 auto;
			text-align: center;
			font-size: 18px;
			color: #484848;
			font-family: "Open Sans",sans-serif;
			font-weight: 300;
		}

		.container a
		{
			font-weight: 600;
			color: #2A94C8;
		}
	</style>
</head>
<body>
	<div class="container error-box">
		<p><?php _e( 'This landing page is not available.' ); ?></p>

		<?php if( $message !== null )
		{
			echo '<p>' . $message . '</p>';
		}
		?>

		<p><?php echo __( 'If you are the Page Admin, please log-in and check your account status.' ); ?></p>
	</div>
</body>
</html>
