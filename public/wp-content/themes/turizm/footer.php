</div>
</div>
<footer class="main-footer">
	<div class="content-footer">
		<?php if ( has_nav_menu( 'bottom' ) ) {
			echo get_menu_footer_with_tur_classes(); } ?>
		<div class="copyright-wrap">
			<div class="copyright-text">Туристик
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="copyright-text__link"> loftschool <?php echo date( 'Y' ); ?></a>
			</div>
		</div>
	</div>
</footer>
</div>
<!-- wrapper_end-->
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script src="/js/main.js"></script>
<?php wp_footer(); ?>
</body>
</html>
