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
