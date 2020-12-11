<?php
/* Smarty version 3.1.29, created on 2020-12-11 18:20:17
  from "/www/wwwroot/whmcs/templates/six/includes/verifyemail.tpl" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.29',
  'unifunc' => 'content_5fd347e1d227c2_42351639',
  'file_dependency' => 
  array (
    'b14d426284cbfdb941f41e3cec664128cdc96da7' => 
    array (
      0 => '/www/wwwroot/whmcs/templates/six/includes/verifyemail.tpl',
      1 => 1607664436,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5fd347e1d227c2_42351639 ($_smarty_tpl) {
if ($_smarty_tpl->tpl_vars['emailVerificationIdValid']->value) {?>
    <div class="email-verification success">
        <div class="container">
            <i class="fa fa-check"></i>
            <span class="text"><?php echo $_smarty_tpl->tpl_vars['LANG']->value['emailAddressVerified'];?>
</span>
        </div>
    </div>
<?php } elseif ($_smarty_tpl->tpl_vars['emailVerificationIdValid']->value === false) {?>
    <div class="email-verification failed">
        <div class="container">
            <div class="row">
                <div class="col-xs-2 col-xs-push-10 col-sm-1 col-sm-push-11">
                    <button type="button" class="btn close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="col-xs-10 col-xs-pull-2 col-sm-7 col-sm-pull-1 col-md-8">
                    <i class="fa fa-times-circle"></i>
                    <span class="text"><?php echo $_smarty_tpl->tpl_vars['LANG']->value['emailKeyExpired'];?>
</span>
                </div>
                <div class="col-xs-12 col-sm-4 col-md-3 col-sm-pull-1">
                    <button id="btnResendVerificationEmail" class="btn btn-default btn-sm btn-block">
                    <?php echo $_smarty_tpl->tpl_vars['LANG']->value['resendEmail'];?>

                </button>
                </div>
            </div>
        </div>
    </div>
<?php } elseif ($_smarty_tpl->tpl_vars['emailVerificationPending']->value && !$_smarty_tpl->tpl_vars['showingLoginPage']->value) {?>
    <div class="email-verification">
        <div class="container">
            <div class="row">
                <div class="col-xs-2 col-xs-push-10 col-sm-1 col-sm-push-11">
                    <button type="button" class="btn close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="col-xs-10 col-xs-pull-2 col-sm-7 col-sm-pull-1 col-md-8">
                    <i class="fa fa-warning"></i>
                    <span class="text"><?php echo $_smarty_tpl->tpl_vars['LANG']->value['verifyEmailAddress'];?>
</span>
                </div>
                <div class="col-xs-12 col-sm-4 col-md-3 col-sm-pull-1">
                    <button id="btnResendVerificationEmail" class="btn btn-default btn-sm btn-block btn-resend-verify-email">
                        <?php echo $_smarty_tpl->tpl_vars['LANG']->value['resendEmail'];?>

                    </button>
                </div>
            </div>
        </div>
    </div>
<?php }
}
}
