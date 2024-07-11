<?php
$userAgent = $_SERVER['HTTP_USER_AGENT'];
/**
 * @author yanroussel
 *         Limitation à FF53 (contrainte DSFR)
 */
$browser = null;
$version = null;
if(preg_match('/(?<browser>Firefox|Chrome)\/(?<version>\d+)/', $userAgent, $matches)) {
  $version = (int)$matches['version'];
  $browser = $matches['browser'];
}
switch($browser) {
  case 'Firefox':
    if($version < 53) {
      echo str_replace(['#version#','#browser#'],[$version, $browser],file_get_contents('compatibilite-impossible.html'));
      die;
    }
  case 'Chrome':
    if($version < 51) {
      echo str_replace(['#version#','#browser#'],[$version, $browser],file_get_contents('compatibilite-impossible.html'));
      die;
    }
  default:
}
