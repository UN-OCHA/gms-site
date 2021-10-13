<?php

/**
 * @file
 * Returns the HTML for a single Drupal page.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728148
 */
?>

<?php
global $user, $_domain;
?>
<div class="login_text">
  <div class="float_l">
    <?php if ($user->uid) :  // phpcs:Ignore ?>
      <span><?php print t('Welcome, !username', array('!username' => l($user->name, 'user/' . $user->uid, array('html' => TRUE)))); ?></span>
    <?php endif; ?>
  </div>
  <div class="float_r" onload="display_ct();">
      <span id='clock'> </span>
      <?php if ($user->uid) : ?>
          <span id='user-logout' class="float_r"><?php print " | <a href='" . url('user/logout') . "'>" . t('Logout') . "</a>"; ?></span>
      <?php else: ?>
      <?php if($_domain['machine_name'] == 'localhost_gms_unocha_org_donor_portal'): ?>
          <?php // phpcs:Ignore ?>
          <span id='welcome-user-login' class="float_r"><?php print " | <a href='" . url('hid/login') . "'>" . t('Donor Login') . "</a> | <a href='" . url('user/login') . "'>" . t('Admin Login') . "</a>"; ?></span>
      <?php endif; ?>
      <?php if(!drupal_is_front_page() && $_domain['machine_name'] != 'localhost_gms_unocha_org_donor_portal') : ?>
                  <span id='welcome-user-login' class="float_r"><?php print " | <a href='" . url('user/login') . "'>" . t('Login') . "</a>"; ?></span>
      <?php endif; ?>
      <?php endif; ?>
  </div>
</div>
