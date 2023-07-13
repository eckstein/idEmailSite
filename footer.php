</main>
</div>
<footer id="footer" role="contentinfo">
<div id="footerUserInfo">
<?php
// Check if a user is logged in
if ( is_user_logged_in() ) {
  // Get the current user's info
  $current_user = wp_get_current_user();
  // Display the username and logout link
  echo 'Logged in as ' . esc_html( $current_user->user_login ) . ' <a href="' . esc_url( wp_logout_url() ) . '">Logout</a>';
}
?>

</div>
<div id="copyright">
&copy; <?php echo esc_html( date_i18n( __( 'Y', 'idEmailSite' ) ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
</div>
</footer>
</div>
<?php wp_footer(); ?>
</body>
<div id="iDoverlay"></div>
<div id="iDspinner" class="loader"></div>
</html>
