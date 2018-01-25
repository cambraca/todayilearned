<?php

// This code can run in /devel/php

$lang = 'go';
$tag = $lang;

$config = \Drupal::service('config.factory')->getEditable('geshifilter.settings');
$config->set('language.'.$lang.'.enabled', true);
$config->set('language.'.$lang.'.tags', $tag);
$config->save();

$config = \Drupal::config('geshifilter.settings');
dpm($config->get('language'));

