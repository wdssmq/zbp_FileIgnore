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
  $act = GetVars("act", "GET");
  if ($act !== "PluginEnb" && $act !== "ThemeSet") {
    return false;
  }
  if ($act == "PluginEnb") {
    $appInfo = array("plugin", GetVars('name', 'GET'));
  } else {
    $appInfo = array("theme", GetVars('theme', 'POST'));
  }
  $app = new App();
  if ($app->LoadInfoByXml($appInfo[0], $appInfo[1]) == false) {
    return;
  }
  $ID = $app->id;
  $dir = $app->GetDir();

  // 遍历依赖插件并启用，仍然会报错，但是刷新下就好
  $ad = explode('|', $app->advanced_dependency);
  foreach ($ad as $d) {
    if (!$d) {
      continue;
    }
    EnablePlugin($d);
  }

  // 通过创建并删除文件更新应用文件夹的时间
  $file = "{$dir}DeleteMe.txt";
  file_put_contents($file, "请删除这个文件");
  unlink($file);

  // 自动生成工作区文件
  $sFile = FileIgnore_Path("u-vscode");
  $dFile = "{$dir}{$ID}.code-workspace";
  if (is_file($sFile) && !is_file($dFile)) {
    $conVSCode = file_get_contents($sFile);
    $conVSCode = str_replace("-zbp_path-", str_replace("\\", '\\\\', ZBP_PATH), $conVSCode);
    file_put_contents($dFile, $conVSCode);
  }

  // 自动创建zbignore
  // $sFile = FileIgnore_Path("u-zbignore");
  // $dFile = "{$dir}zbignore.txt";
  // if (is_file($sFile) && !is_file($dFile)) {
  //   copy($sFile, $dFile);
  // }

  // 应用中心开发模式开启
  $zbp->Config('AppCentre')->enabledevelop = true;
  $zbp->SaveConfig('AppCentre');
  // zblog 开发模式
  $zbp->option['ZC_DEBUG_MODE'] = true;
  $zbp->SaveOption();
}
function FileIgnore_update($app, &$dirs, &$files)
{
  // global $zbp;
  $modified = date('Y-m-d', time());
  if ($modified !== $app->modified) {
    $app->modified = $modified;
  } else {
    $arr = explode(".", $app->version);
    if (count($arr) === 2) {
      $arr[] = 0;
    }
    $arr[2]++;
    if ($arr[2] >= 100) {
      $arr[1]++;
      $arr[2] = 0;
    }
    $app->version = join(".", $arr);
  }
  $app->SaveInfoByXml();
  $dir = $app->GetDir();
  if ($app->type === "theme") {
    $arrCssFiles = $app->GetCssFiles();
    $conCssFile = file_get_contents(array_shift($arrCssFiles));
    $hash = crc32($conCssFile);
    $curFile = "{$dir}template/header.php";
    $conFile = file_get_contents($curFile);
    file_put_contents($curFile, preg_replace("/(\.css|\.js)[^\"']*/", "$1?v=$hash", $conFile));
  }
  if (!empty($app->include)) {
    $curFile = "{$dir}{$app->include}";
    $conFile = file_get_contents($curFile);
    $conFile = preg_replace("/\?v=[0-9\-]{10}/", "?v={$app->modified}", $conFile);
    file_put_contents($curFile, $conFile);
  }
}
function FileIgnore_main($app, &$dirs, &$files)
{
  global $zbp;
  InstallPlugin_FileIgnore();
  $FileIgnorePath = plugin_dir_path(__FILE__);
  $AppPath        = $app->GetDir();
  if (is_file($AppPath . "zbignore.txt")) {
    return;
  }
  $parser = array();
  $parser = FileIgnore_getIgnoreFilses($FileIgnorePath . ".gitignore", $AppPath,  $parser);
  if (is_file($AppPath . ".gitignore")) {
    $parser = FileIgnore_getIgnoreFilses($AppPath . ".gitignore", $AppPath, $parser);
  }
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
    if (in_array($files[$i], $parser) || in_array(dirname($files[$i]), $parser)) {
      file_put_contents($LogPath, $files[$i] . " 排除\n\n", FILE_APPEND);
      $files[$i] = "";
    } elseif ($files[$i] == "{$AppPath}.gitignore") {
      file_put_contents($LogPath, $files[$i] . " 排除\n\n", FILE_APPEND);
      $files[$i] = "";
    } else {
      file_put_contents($LogPath, $files[$i] . " 打包\n\n", FILE_APPEND);
    }
    // 强制包含规则有问题，也不是很需要
  }
  $dirs = FileIgnore_ClearArray($dirs);
  $files = FileIgnore_ClearArray($files);
  // die();
}
function FileIgnore_getIgnoreFilses($rulFile, $AppPath, $matches = array())
{
  $lines = file($rulFile);
  $dir = trim($AppPath, "/");
  foreach ($lines as $line) {
    // var_dump($matches);
    $line = trim($line);
    if ($line === '') continue;                 # empty line
    if (substr($line, 0, 1) == '#') continue;   # a comment
    if (substr($line, 0, 1) == '!') {           # negated glob
      $line = substr($line, 1);
      $files = array_merge(glob("{$dir}{$line}"), glob("{$dir}{$line}/*"));
      $matches = array_diff($matches, $files);
    } else {                                    # normal glob
      $files = array_merge(glob("{$dir}{$line}"), glob("{$dir}{$line}/*"));
      // $files = glob("$line");
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
    case 'u-zbignore':
      return $result . 'usr/zbignore.txt';
      break;
    case 'u-vscode':
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