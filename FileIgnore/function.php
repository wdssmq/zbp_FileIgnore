<?php

$edtList = array(
  "Neditor" => "Neditor",
  "Editormd" => "Editor.md",
  "iceEditor" => "iceEditor",
  "UEditor" => "UEditor",
);

function FileIgnore_GetCurEdt()
{
  global $zbp, $edtList;
  $curEdt = "";
  foreach ($edtList as $key => $value) {
    if ($zbp->CheckPlugin("{$key}")) {
      $curEdt = $key;
      break;
    }
  }
  return $curEdt;
}

function FileIgnore_SetCurEdt($edt)
{
  global $zbp, $edtList;
  // 获取当前编辑器
  $oldEdt = FileIgnore_GetCurEdt();
  if ($oldEdt == $edt) {
    return;
  }
  // 停用当前编辑器
  if (!empty($oldEdt)) {
    DisablePlugin($oldEdt);
  }
  // $zbp->activedapps 内移除当前编辑器
  $zbp->activedapps = array_diff($zbp->activedapps, array($oldEdt));
  // 启用新编辑器
  EnablePlugin($edt);
}

/**
 * ↓ 插件按分组切换启用
 */

/**
 * 获取插件分组配置
 * @return object
 * @property bool $hasGroup 是否有分组配置
 * @property array $group 分组配置
 */
function FileIgnore_GetPlugGroupCFG()
{
  $uFile = FileIgnore_Path("u-plugin-group");
  $cfgData = (object)array("hasGroup" => false, "group" => array());
  if (file_exists($uFile)) {
    $cfgData->hasGroup = true;
    $cfgData->group = json_decode(file_get_contents($uFile));
  }
  return $cfgData;
}

// 切换分组配置构建表单
function FileIgnore_BuildPlugGroupForm($cfgPlugGroup, $cur = 0)
{
  if ($cfgPlugGroup->hasGroup) {
    // 从分组中去掉「固定分组」返回新数组
    $group = array_filter($cfgPlugGroup->group, function ($value) {
      return !isset($value->fixed) || !$value->fixed;
    });
    $selectArr = array();
    foreach ($group as $key => $value) {
      $selectArr[$key] = $value->name;
    }
    ZbpForm::$setreturn = true;
    return zbpform::select("read_switchTo", $selectArr, $cur, "$('#read_switch+.imgcheck').click();");
  }
}

// 执行插件切换
function FileIgnore_PlugGroupSwitch($to = 1)
{
  global $zbp;
  $pickPlugList = array();
  $cfgPlugGroup = FileIgnore_GetPlugGroupCFG();
  foreach ($cfgPlugGroup->group as $key => $value) {
    // 合并「固定分组」内的插件
    if (isset($value->fixed) && $value->fixed) {
      $pickPlugList = array_merge($pickPlugList, $value->list);
    }
    // 如果是目标分组，合并插件
    if ($key == $to) {
      $pickPlugList = array_merge($pickPlugList, $value->list);
    }
  }
  // 启用插件
  // 拼接后赋值给 $zbp->option['ZC_USING_PLUGIN_LIST']
  $zbp->option['ZC_USING_PLUGIN_LIST'] = implode("|", $pickPlugList);
  $zbp->SaveOption();
  // 设置提示
  $zbp->SetHint('good', '插件分组切换成功');
}
