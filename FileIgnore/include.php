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
  // file_put_contents($LogPath, json_encode($files));
  for ($i = 0; $i < count($dirs); $i++) {
    file_put_contents($LogPath, "$i 、", FILE_APPEND);
    if ($parser->checkPath(str_replace($AppPath, "", $dirs[$i]))) {
      file_put_contents($LogPath, $dirs[$i] . " 排除\n\n", FILE_APPEND);
      $dirs[$i] = "";
    } else{
      file_put_contents($LogPath, $dirs[$i] . " 打包\n\n", FILE_APPEND);      
    }
  }
  file_put_contents($LogPath, "------\n\n", FILE_APPEND);
  for ($i = 0; $i < count($files); $i++) {
    file_put_contents($LogPath, "$i 、", FILE_APPEND);
    if ($parser->checkPath(str_replace($AppPath, "", $files[$i])) || $IgnorePath == $files[$i]) {
      file_put_contents($LogPath, $files[$i] . " 排除\n\n", FILE_APPEND);
      $files[$i] = "";
    } elseif (!in_array(dirname($files[$i]) . "/", $dirs) && dirname($files[$i]) . "/" != $AppPath) {
      file_put_contents($LogPath, dirname($files[$i]) . "/\n", FILE_APPEND);
      file_put_contents($LogPath, $files[$i] . "\n", FILE_APPEND);
      $dirs[] = dirname($files[$i]) . "/";
    } else {
      file_put_contents($LogPath, $files[$i] . " 打包\n\n", FILE_APPEND);
    }
  }
  $dirs = FileIgnore_ClearArray($dirs);
  $files = FileIgnore_ClearArray($files);
}
function FileIgnore_ClearArray($arr){
  $result = array();
  for ($i = 0; $i < count($arr); $i++) {
    if($arr[$i] != "")
      $result[] = $arr[$i];
  }
  return $result;
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