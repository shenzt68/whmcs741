<?php
/* Smarty version 3.1.29, created on 2020-12-11 18:20:17
  from "/www/wwwroot/whmcs/templates/six/includes/navbar.tpl" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.29',
  'unifunc' => 'content_5fd347e1d107c6_22557205',
  'file_dependency' => 
  array (
    '57ba3f2d00d5063126900b07bb8ca28eaab4579e' => 
    array (
      0 => '/www/wwwroot/whmcs/templates/six/includes/navbar.tpl',
      1 => 1607664436,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5fd347e1d107c6_22557205 ($_smarty_tpl) {
$_from = $_smarty_tpl->tpl_vars['navbar']->value;
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$__foreach_item_0_saved_item = isset($_smarty_tpl->tpl_vars['item']) ? $_smarty_tpl->tpl_vars['item'] : false;
$_smarty_tpl->tpl_vars['item'] = new Smarty_Variable();
$_smarty_tpl->tpl_vars['item']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->_loop = true;
$__foreach_item_0_saved_local_item = $_smarty_tpl->tpl_vars['item'];
?>
    <li menuItemName="<?php echo $_smarty_tpl->tpl_vars['item']->value->getName();?>
" class="<?php if ($_smarty_tpl->tpl_vars['item']->value->hasChildren()) {?>dropdown<?php }
if ($_smarty_tpl->tpl_vars['item']->value->getClass()) {?> <?php echo $_smarty_tpl->tpl_vars['item']->value->getClass();
}?>" id="<?php echo $_smarty_tpl->tpl_vars['item']->value->getId();?>
">
        <a <?php if ($_smarty_tpl->tpl_vars['item']->value->hasChildren()) {?>class="dropdown-toggle" data-toggle="dropdown" href="#"<?php } else { ?>href="<?php echo $_smarty_tpl->tpl_vars['item']->value->getUri();?>
"<?php }
if ($_smarty_tpl->tpl_vars['item']->value->getAttribute('target')) {?> target="<?php echo $_smarty_tpl->tpl_vars['item']->value->getAttribute('target');?>
"<?php }?>>
            <?php if ($_smarty_tpl->tpl_vars['item']->value->hasIcon()) {?><i class="<?php echo $_smarty_tpl->tpl_vars['item']->value->getIcon();?>
"></i>&nbsp;<?php }?>
            <?php echo $_smarty_tpl->tpl_vars['item']->value->getLabel();?>

            <?php if ($_smarty_tpl->tpl_vars['item']->value->hasBadge()) {?>&nbsp;<span class="badge"><?php echo $_smarty_tpl->tpl_vars['item']->value->getBadge();?>
</span><?php }?>
            <?php if ($_smarty_tpl->tpl_vars['item']->value->hasChildren()) {?>&nbsp;<b class="caret"></b><?php }?>
        </a>
        <?php if ($_smarty_tpl->tpl_vars['item']->value->hasChildren()) {?>
            <ul class="dropdown-menu">
            <?php
$_from = $_smarty_tpl->tpl_vars['item']->value->getChildren();
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$__foreach_childItem_1_saved_item = isset($_smarty_tpl->tpl_vars['childItem']) ? $_smarty_tpl->tpl_vars['childItem'] : false;
$_smarty_tpl->tpl_vars['childItem'] = new Smarty_Variable();
$_smarty_tpl->tpl_vars['childItem']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['childItem']->value) {
$_smarty_tpl->tpl_vars['childItem']->_loop = true;
$__foreach_childItem_1_saved_local_item = $_smarty_tpl->tpl_vars['childItem'];
?>
                <li menuItemName="<?php echo $_smarty_tpl->tpl_vars['childItem']->value->getName();?>
"<?php if ($_smarty_tpl->tpl_vars['childItem']->value->getClass()) {?> class="<?php echo $_smarty_tpl->tpl_vars['childItem']->value->getClass();?>
"<?php }?> id="<?php echo $_smarty_tpl->tpl_vars['childItem']->value->getId();?>
">
                    <a href="<?php echo $_smarty_tpl->tpl_vars['childItem']->value->getUri();?>
"<?php if ($_smarty_tpl->tpl_vars['childItem']->value->getAttribute('target')) {?> target="<?php echo $_smarty_tpl->tpl_vars['childItem']->value->getAttribute('target');?>
"<?php }?>>
                        <?php if ($_smarty_tpl->tpl_vars['childItem']->value->hasIcon()) {?><i class="<?php echo $_smarty_tpl->tpl_vars['childItem']->value->getIcon();?>
"></i>&nbsp;<?php }?>
                        <?php echo $_smarty_tpl->tpl_vars['childItem']->value->getLabel();?>

                        <?php if ($_smarty_tpl->tpl_vars['childItem']->value->hasBadge()) {?>&nbsp;<span class="badge"><?php echo $_smarty_tpl->tpl_vars['childItem']->value->getBadge();?>
</span><?php }?>
                    </a>
                </li>
            <?php
$_smarty_tpl->tpl_vars['childItem'] = $__foreach_childItem_1_saved_local_item;
}
if ($__foreach_childItem_1_saved_item) {
$_smarty_tpl->tpl_vars['childItem'] = $__foreach_childItem_1_saved_item;
}
?>
            </ul>
        <?php }?>
    </li>
<?php
$_smarty_tpl->tpl_vars['item'] = $__foreach_item_0_saved_local_item;
}
if ($__foreach_item_0_saved_item) {
$_smarty_tpl->tpl_vars['item'] = $__foreach_item_0_saved_item;
}
}
}
