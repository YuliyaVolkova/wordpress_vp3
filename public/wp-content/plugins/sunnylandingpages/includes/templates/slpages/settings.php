<script type="text/javascript">
	var slpages_post_type_area = true;
	<?php
		if( $user_id )
		{
			echo 'window.location="'. admin_url( 'edit.php?post_type=slpages_post' ) .'";' ;
		}
	?>
</script><div class="bootstrap-wpadmin" align="center">
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<div style="background:#F88335">
			<!--<img src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/logo.png';?> "> </img>-->
			<img src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/banner4.jpg';?> "> </img>
		
		</div>
		<div class="login-form slpages-form" align="center">
				
			<div class="login-box">
				<?php if( $error ): ?>
					<div class="error"><?php echo $error; ?></div>
				<?php endif; ?>

				<form method="post" action="<?php echo admin_url( 'options-general.php?page='. $plugin_file ); ?>">
					<?php if( !$user_id ): ?>
							<div style="padding-top: 5%;padding-bottom:5%;background:#f8f8f8">
								<a href="https://sunnylandingpages.com/user/login" class="button button-primary slpages-form-h3-a" target="_blank" style=" 	
										font-weight:bold;
										width:33%;
									    background: #57A773;
										background-color: #57a773;
										border: 1px #57A773;
										height: auto;
										padding: 8px 0px 8px 0px;
										font-size: 18px;
										box-shadow: 0 1px 0 #57a773;
										-webkit-box-shadow: 0 1px 0 #57a773;
										text-shadow: 0 -1px 1px #57A773, 1px 0 1px #57A773, 0 1px 1px #57A773, -1px 0 1px #57A773;
										color: #f8f8f8;
									">Sign up on the website in 1 min</a>
							</div>
							<div style="padding-top: 1%; padding-bottom:6%; background:#ffffff">	
							<input type="hidden" name="slpages_meta_box_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>" />
							<div class="row-fluid form-horizontal">
								<h3>Already have an account?</h3>
								<div style="padding-top:0.5%">
									<input type="text" name="email" placeholder="Email" />
								</div>
								<div style="padding:0.5% 0">
									<input type="password" name="password" placeholder="Password" />
								</div>

								<div>
									<input style = "
									    font-weight:bold;
										background: #08B2E3;
										border: 1px #034153;
										-webkit-box-shadow: 0 1px 0 #034153;
										box-shadow: 0 1px 0 #08B2E3;
										color: #ffffff;
										text-decoration: none;
										text-shadow: 0 -1px 1px #08B2E3, 1px 0 1px #08B2E3, 0 1px 1px #08B2E3, -1px 0 1px #08B2E3;
									"type="submit" class="button button-primary login-button" value="Log In"/>
								</div>
							</div>
						</div>
					<?php else: ?>
						<?php if( $user ): ?>
							<h3>You are logged in as <?php echo $user; ?></h3>
						<?php else: ?>
							<h3>You are not properly connected</h3>
							<p>Please click 'disconnect' and connect again. It is safe process, your pages will keep working.</p>
						<?php endif; ?>
						<input type="hidden" name="action" value="disconnect" />
						<input type="submit" class="button button-primary" value="Disconnect" />
					<?php endif; ?>
				</form>
			</div>

			<div style="clear: both"></div>
		</div>

		
		
		
	</div>
</div>



<div align="center" style="padding-top:1%;padding-bottom:3%">
	<h1 style="padding-bottom:1%">Grow Your Online Sales</h1>
	<h2>Sunny Landing Pages is the most trusted way to grow your business</h2>
	<h2 style="padding-bottom:0.3%">used by 1500+ websites</h2>
	<ul style="font-size:1.2em;">
		<li style="padding-bottom:0.5%">✓  All the tools you need to publish a landing page to your site in 1 place</li>
		<li style="padding-bottom:0.5%">✓  No coding needed</li>
		<li style="padding-bottom:0.5%">✓  Get started in 1 minute</li>
		<li style="padding-bottom:0.5%">✓  Great Customer Support</li>
	</ul>
</div>



<div style="background:#f8f8f8;padding:0 4%; padding-top:3%;padding-bottom:6%" align="center">
	<h2 style="padding-bottom:1.7%;font-size:1.7em">Select From 100+ Templates</h2>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template1.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template2.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template3.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template4.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template5.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template6.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template7.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template8.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template9.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template10.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template11.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template12.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template13.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template14.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template15.jpg';?> "> </img>
	<img style="padding:1% 1%" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/template16.jpg';?> "> </img>
	
	
</div>

<div align="center" style="padding-top:2%; padding-bottom:6%" >
	
	<h2 style="padding-bottom:1.8%;font-size:1.7em">		Publish to your site in just 4 steps! </h2>

	
	<ul style="font-size:1.2em; padding:0 31%" align="left">
		<li  style="padding-bottom:2%">1. Create a free account on the website. No credit card required.</li>
		<li  style="padding-bottom:2%">2. Select a template and edit it to create your page. </li>
		<li  style="padding-bottom:2%">3. Login to the Wordpress plugin.</li>
		<li  style="padding-bottom:2%">4. Publish the page to your site with 1 click.</li>
	</ul>

</div>	



<div align="center" style="font-size:1em;padding:2% 0; padding-bottom:4%;background:#fff">
	
	<h2 style="padding-bottom:3%;font-size:1.7em">Customer Speak</h2>
	<div style="padding:0 30%">
	<img style=""src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/rjs-mike-malan1.jpg';?> "> </img>
	
		<div style="font-style:italic;padding-top:2%"> 
		Being a small business and not having an in-house web developer or IT team, Sunny Landing Pages was the way to go for us. They were helpful and responsive; and got us going quickly on the best landing page tool out there.
		</div>
		
		
	</div>
	<div style="font-size:1.3em;font-style: italic;font-weight:bold; " >
		<br/>Mike Malan | Director, RJS Pest | NYC, USA
	</div>
	<br/><br/><br/><br/>

	<div style="padding:0 30%">
		<img style="" src="<?php echo SLPAGES_PLUGIN_URI . 'assets/img/lee-richards1.jpg';?> "> </img>
		<div style="font-style: italic;padding-top:2%">
		For creating landing pages or just digital marketing in general, I would strongly recommend talking to Vin at Sunny Landing Pages. 
		</div>
		
	</div>
	<div style="font-size:1.3em;font-style: italic;font-weight:bold; ">
		<br/>Lee Richards | Author | Macroom, Ireland
	</div>
</div>



<style type="text/css">
.slpages-form-h3-a{
	width: 370px;
	text-align: center;
}
.slpages-form input{
	width : 220px;
}
.slpages-form
{
	padding: 20px 0;
	padding-top:0;
}

.slpages-form input
{
	margin-bottom: 5px;
}

.login-box
{
	float: center;
}

.facebook-twitter-message
{
	padding: 10px;
	width: 400px;
	float: left;
}

.slp-comingsoon{
	display:none;
}

h1,h2,h3{
	color:#444;
}

</style>