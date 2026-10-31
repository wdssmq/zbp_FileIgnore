<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
require 'function.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
  $zbp->ShowError(6);
  die();
}
if (!$zbp->CheckPlugin('FileIgnore')) {
  $zbp->ShowError(48);
  die();
}

$act = GetVars('act', 'GET');
$suc = GetVars('suc', 'GET');
if ($act == 'save') {
  CheckIsRefererValid();
  // 修改编辑器
  $edt = GetVars("read_edt", "POST");
  FileIgnore_SetCurEdt($edt);
  // 切换插件启用
  $switch = GetVars("read_switch", "POST");
  $switchTo = GetVars("read_switchTo", "POST");
  if ($switch == 1) {
    FileIgnore_PlugGroupSwitch($switchTo);
  }
  // 保存配置项
  foreach ($_POST as $key => $val) {
    if (substr($key, 0, 5) == 'read_') {
      continue;
    }
    $zbp->Config('FileIgnore')->$key = trim($val);
  }
  $zbp->SaveConfig('FileIgnore');
  $zbp->BuildTemplate();
  $zbp->SetHint('good');
  Redirect('./main.php' . ($suc == null ? '' : "?act={$suc}"));
}

$cfgPlugGroup = FileIgnore_GetPlugGroupCFG();


$blogtitle = '开发者工具';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
  <div class="divHeader">
    <?php echo $blogtitle; ?>
  </div>
  <div class="SubMenu">
    <a href="main.php" title="首页"><span class="m-left m-now">首页</span></a>
  </div>
  <div id="divMain2">
    <form action="<?php echo BuildSafeURL("main.php?act=save"); ?>" method="post">
      <table width="100%" class="tableBorder">
        <tr>
          <th width="10%">项目</th>
          <th>内容</th>
          <th width="45%">说明</th>
        </tr>
        <tr>
          <td>应用作者名</td>
          <td><?php zbpform::text("devName", $zbp->Config("FileIgnore")->devName, "90%"); ?></td>
          <td>仅针对指定作者名下的应用进行某些操作</td>
        </tr>
        <tr>
          <td>配置文件</td>
          <td><?php echo FileIgnore_IsFile(); ?></td>
          <td>启用或打包应用时会自动复制到应用文件夹；</td>
        </tr>
        <tr>
          <td>编辑器切换</td>
          <td><?php zbpform::radio('read_edt', $edtList, FileIgnore_GetCurEdt());; ?></td>
          <td></td>
        </tr>
        <tr>
          <td>切换插件启用</td>
          <td>
            <?php if ($cfgPlugGroup->hasGroup) {
              echo FileIgnore_BuildPlugGroupForm($cfgPlugGroup, 0);
              echo '<label for="read_switch">执行切换：</label>';
              echo zbpform::zbradio('read_switch', 0);
            } else {
              echo '无分组配置，请复制一份 /usr_cfg/plugin-group.json 到 /usr/plugin-group.json';
            } ?>

          </td>
          <td>
            启用「指定分组 + 固定分组」中的插件，停用其他插件
          </td>
        </tr>
        <tr>
          <td></td>
          <td colspan="2"><input type="submit" value="提交" /><small><a title="刷新" href="javascript:;" onclick="location.reload();" style="font-size: 16px;display: inline-block;margin-left: 5px;">刷新</a></small></td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php require "about.php"; ?>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
