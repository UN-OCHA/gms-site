<?php

/**
 * @file
 * Return themed HTML.
 */
?>
<div class="container">
    <div class="row row-1">
      <div class="pooled-fund-donation col-xs-12 col-sm-12 col-md-3 col-lg-3">
        <span class="title"><?php print $variables['element']['#values']['donation']['title']?></span>
            <span class="value"><?php print $variables['element']['#values']['donation']['value']?></span>
      </div>
      <div class="pooled-fund-allocations col-xs-12 col-sm-12 col-md-3 col-lg-3">
        <span class="title"><?php print $variables['element']['#values']['allocations']['title']?></span>
            <span class="value"><?php print $variables['element']['#values']['allocations']['value']?></span>
      </div>
      <div class="pooled-fund-donors col-xs-12 col-sm-12 col-md-3 col-lg-3">
        <span class="title"><?php print $variables['element']['#values']['donors']['title']?></span>
            <span class="value"><?php print $variables['element']['#values']['donors']['value']?></span>
      </div>
      <div class="pooled-fund-countries col-xs-12 col-sm-12 col-md-3 col-lg-3">
        <span class="title"><?php print $variables['element']['#values']['countries']['title']?></span>
            <span class="value"><?php print $variables['element']['#values']['countries']['value']?></span>
      </div>
    </div>
    <div class="row row-2">
      <div class="pooled-fund-target col-xs-12 col-sm-12 col-md-3 col-lg-3">
        <span class="title"><?php print $variables['element']['#values']['target']['title']?></span>
            <span class="value"><?php print $variables['element']['#values']['target']['value']?></span>
      </div>
      <div class="pooled-fund-reached col-xs-12 col-sm-12 col-md-3 col-lg-3">
        <span class="title"><?php print $variables['element']['#values']['reached']['title']?></span>
            <span class="value"><?php print $variables['element']['#values']['reached']['value']?></span>
      </div>
      <div class="pooled-fund-projects col-xs-12 col-sm-12 col-md-3 col-lg-3">
        <span class="title"><?php print $variables['element']['#values']['projects']['title']?></span>
            <span class="value"><?php print $variables['element']['#values']['projects']['value']?></span>
      </div>
      <div class="pooled-fund-partners col-xs-12 col-sm-12 col-md-3 col-lg-3">
        <span class="title"><?php print $variables['element']['#values']['partners']['title']?></span>
            <span class="value"><?php print $variables['element']['#values']['partners']['value']?></span>
      </div>
    </div>
  </div>
