<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
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

$blogtitle = 'FileIgnore';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
  <div class="divHeader">
    <?php echo $blogtitle; ?>
    <small><a title="刷新" href="javascript:;" onclick="location.reload();" style="font-size: 16px;display: inline-block;margin-left: 5px;">刷新</a></small>
  </div>
  <div class="SubMenu">
    <a href="main.php" title="首页"><span class="m-left m-now">首页</span></a>
    <?php require_once "about.php"; ?>
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
          <td></td>
          <td colspan="2"><input type="submit" value="提交" /><small><a title="刷新" href="javascript:;" onclick="location.reload();" style="font-size: 16px;display: inline-block;margin-left: 5px;">刷新</a></small></td>
        </tr>
      </table>
    </form>
  </div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
