<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_header.tpl"}-->
<script type="text/javascript">
</script>

<h2><!--{$tpl_subtitle}--></h2>
<form name="form1" id="form1" method="post" action="<!--{$smarty.server.REQUEST_URI|h}-->">
<input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
<input type="hidden" name="mode" value="edit">
<p>Omiseの設定が行えます。<br/>
    <br/>
</p>

<table border="0" cellspacing="1" cellpadding="8" summary=" ">
    <tr>
        <td colspan="2" width="90" bgcolor="#f3f3f3">▼OmiseAPI接続設定</td>
    </tr>
    <tr >
        <td bgcolor="#f3f3f3">Public Key<span class="red">※</span></td>
        <td>
        <!--{assign var=key value="pkey"}-->
        <span class="red"><!--{$arrErr[$key]}--></span>
        <input type="text" name="skey" value="<!--{$arrForm[$key]|h}-->" placeholder="pkey_xxxxxxxxxxxxxxxxxxx" />
        </td>
    </tr>
    <tr>
        <td bgcolor="#f3f3f3">Secret Key<span class="red">※</span></td>
        <td>
        <!--{assign var=key value="skey"}-->
        <span class="red"><!--{$arrErr[$key]}--></span><br />
        <input type="text" name="skey" value="<!--{$arrForm[$key]|h}-->" placeholder="skey_xxxxxxxxxxxxxxxxxxx" />
        </td>
    </tr>
</table>

<div class="btn-area">
    <ul>
        <li>
            <a class="btn-action" href="javascript:;" onclick="document.form1.submit();return false;"><span class="btn-next">この内容で登録する</span></a>
        </li>
    </ul>
</div>

</form>
<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_footer.tpl"}-->
