<?php
//注册插件
RegisterPlugin("FileIgnore", "ActivePlugin_FileIgnore");
function ActivePlugin_FileIgnore()
{
  Add_Filter_Plugin('Filter_Plugin_App_Pack', 'FileIgnore_main');
}
require_once('ignorepath.php');
function FileIgnore_main($app, &$dirs, &$files)
{
  global $zbp;
  // InstallPlugin_FileIgnore();
  $FileIgnorePath = plugin_dir_path(__FILE__);
  $AppPath        = $app->GetDir();
  $IgnorePath     = $AppPath . ".gitignore";
  if (!file_exists($IgnorePath)) {
    $IgnorePath = $FileIgnorePath . ".gitignore";
  }
  $parser  = new IgnorePath('', $IgnorePath);
  $LogPath = $FileIgnorePath . "cache/log.txt";
  file_put_contents($LogPath, "");
  for ($i = 0; $i < count($dirs); $i++) {
    if ($parser->checkPath(str_replace($AppPath, "", $dirs[$i]))) {
      file_put_contents($LogPath, $dirs[$i] . " 排除\n", FILE_APPEND);
      unset($dirs[$i]);
    }
  }
  for ($i = 0; $i < count($files); $i++) {
    if ($parser->checkPath(str_replace($AppPath, "", $files[$i])) || $IgnorePath == $files[$i]) {
      file_put_contents($LogPath, $files[$i] . " 排除\n", FILE_APPEND);
      unset($files[$i]);
    } elseif (!in_array(dirname($files[$i]) . "/", $dirs) && dirname($files[$i]) . "/" != $AppPath) {
      file_put_contents($LogPath, dirname($files[$i]) . "/\n", FILE_APPEND);
      file_put_contents($LogPath, $files[$i] . "\n", FILE_APPEND);
      $dirs[] = dirname($files[$i]) . "/";
    }
  }
}
function InstallPlugin_FileIgnore()
{
  $FileIgnorePath = plugin_dir_path(__FILE__);
  $IgnorePath     = $FileIgnorePath . ".gitignore";
  if (!file_exists($IgnorePath)) {
    copy($FileIgnorePath . "sources/.gitignore", $IgnorePath);
  }
  if (!file_exists($FileIgnorePath . "cache/")) {
    mkdir($FileIgnorePath . "cache/", 0755);
  }
}
function UninstallPlugin_FileIgnore()
{
}