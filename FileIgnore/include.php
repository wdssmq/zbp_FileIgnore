<?php
//注册插件
RegisterPlugin("FileIgnore", "ActivePlugin_FileIgnore");
function ActivePlugin_FileIgnore()
{
  Add_Filter_Plugin('Filter_Plugin_Cmd_Begin', 'FileIgnore_PluginEnb');
  Add_Filter_Plugin('Filter_Plugin_App_Pack', 'FileIgnore_main');
  Add_Filter_Plugin('Filter_Plugin_App_Pack', 'FileIgnore_update');
}
function FileIgnore_PluginEnb()
{
  global $zbp;
  if (GetVars('act', 'GET') !== "PluginEnb") {
    return false;
  }
  $app = new App();
  if ($app->LoadInfoByXml('plugin', GetVars('name', 'GET')) === true) {
    $ID = $app->id;
    $dir = $app->GetDir();
    $ad = explode('|', $app->advanced_dependency);
    // 遍历依赖插件并启用
    foreach ($ad as $d) {
      if (!$d) {
        continue;
      }
      EnablePlugin($d);
    }
    // 自动生存工作区文件
    $fileVSCode = "{$dir}{$ID}.code-workspace";
    $usrVSCode = FileIgnore_Path("vscode");
    if (!is_file($fileVSCode) && is_file($usrVSCode)) {
      $conVSCode = file_get_contents($usrVSCode);
      $conVSCode = str_replace("-zbp_path-", str_replace("\\", '\\\\', ZBP_PATH), $conVSCode);
      file_put_contents($fileVSCode, $conVSCode);
    }
  }
  // 应用中心开发模式开启
  $zbp->Config('AppCentre')->enabledevelop = true;
  $zbp->SaveConfig('AppCentre');
}
function FileIgnore_update($app, &$dirs, &$files)
{
  // global $zbp;
  $app->modified = date('Y-m-d', time());
  $app->SaveInfoByXml();
  if (!empty($app->include)) {
    $file = "{$app->GetDir()}{$app->include}";
    $fileStr = file_get_contents($file);
    file_put_contents($file, preg_replace("/(\.css|\.js)\?v=[0-9\-]{10}/", "$1?v={$app->modified}", $fileStr));
  }
}
function FileIgnore_main($app, &$dirs, &$files)
{
  global $zbp;
  InstallPlugin_FileIgnore();
  $FileIgnorePath = plugin_dir_path(__FILE__);
  $AppPath        = $app->GetDir();
  $parser = array($FileIgnorePath . ".gitignore", $AppPath . ".gitignore");
  $parser = FileIgnore_getIgnoreFilses($FileIgnorePath . ".gitignore", $AppPath, $parser);
  if (is_file($AppPath . ".gitignore")) {
    $parser = FileIgnore_getIgnoreFilses($AppPath . ".gitignore", $AppPath, $parser);
  }
  // var_dump($parser);

  $LogPath = $FileIgnorePath . "cache/log.txt";
  file_put_contents($LogPath, "");
  file_put_contents($LogPath, json_encode($parser) . "\n");
  for ($i = 0; $i < count($dirs); $i++) {
    file_put_contents($LogPath, "$i 、", FILE_APPEND);
    if (in_array(trim($dirs[$i], "/"), $parser)) {
      file_put_contents($LogPath, $dirs[$i] . " 排除\n\n", FILE_APPEND);
      $dirs[$i] = "";
    } else {
      file_put_contents($LogPath, $dirs[$i] . " 打包\n\n", FILE_APPEND);
    }
  }
  file_put_contents($LogPath, "------\n\n", FILE_APPEND);
  for ($i = 0; $i < count($files); $i++) {
    file_put_contents($LogPath, "$i 、", FILE_APPEND);
    // in_array(dirname($files[$i]), $parser) ||
    if (in_array($files[$i], $parser) || in_array(dirname($files[$i]), $parser)) {
      file_put_contents($LogPath, $files[$i] . " 排除\n\n", FILE_APPEND);
      $files[$i] = "";
    } elseif (!in_array(dirname($files[$i]) . "/", $dirs) && dirname($files[$i]) . "/" != $AppPath) {
      file_put_contents($LogPath, $files[$i] . " 打包\n\n", FILE_APPEND);
      $dirs[] = dirname($files[$i]) . "/";
    } else {
      file_put_contents($LogPath, $files[$i] . " 打包\n\n", FILE_APPEND);
    }
  }
  $dirs = FileIgnore_ClearArray($dirs);
  $files = FileIgnore_ClearArray($files);
  // die();
}
function FileIgnore_getIgnoreFilses($rulFile, $dir, $matches = array())
{
  $dir = trim($dir, "/");
  $lines = file($rulFile);
  foreach ($lines as $line) {
    // var_dump($matches);
    $line = trim($line);
    if ($line === '') continue;                 # empty line
    if (substr($line, 0, 1) == '#') continue;   # a comment
    if (substr($line, 0, 1) == '!') {           # negated glob
      $line = substr($line, 1);
      $files = array_merge(glob("$dir/$line"), glob("$dir/$line/*"));
      $matches = array_diff($matches, $files);
    } else {                                    # normal glob
      $files = array_merge(glob("$dir/$line"), glob("$dir/$line/*"));
      // $files = glob("$dir/$line");
    }
    // var_dump($files);
    $matches = array_merge($matches, $files);
    // var_dump($matches);
  }
  return array_unique($matches);
}
function FileIgnore_ClearArray($arr)
{
  $result = array();
  for ($i = 0; $i < count($arr); $i++) {
    if ($arr[$i] != "")
      $result[] = $arr[$i];
  }
  return $result;
}
function FileIgnore_Path($file, $t = 'path')
{
  global $zbp;
  $result = $zbp->$t . 'zb_users/plugin/FileIgnore/';
  switch ($file) {
    case 'vscode':
      return $result . 'usr/app.code-workspace';
      break;
    case 'usr':
      return $result . 'usr/';
      break;
    case 'var':
      return $result . 'sources/';
      break;
    case 'main':
      return $result . 'main.php';
      break;
    default:
      return $result . $file;
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
