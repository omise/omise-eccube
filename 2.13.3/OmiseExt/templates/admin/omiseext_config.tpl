<!--{*
/**
 * This file is part of EC-CUBE Plugin OmiseExt
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */
*}-->

<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_header.tpl"}-->
<script type="text/javascript"></script>

<h2><!--{$tpl_subtitle}--></h2>
<form name="form1" id="form1" method="post" action="<!--{$smarty.server.REQUEST_URI|h}-->">
  <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
  <input type="hidden" name="mode" value="edit">
  <p>OmiseAPIへの接続設定をしてください。<br>接続キーは<a target="_blank" href="https://dashboard.omise.co/signin">Omise Dashboard</a>から確認できます。<br><br></p>

  <table border="0" cellspacing="1" cellpadding="8" summary=" ">
    <tr>
      <td colspan="2" width="90" bgcolor="#f3f3f3">Omise API keys (LIVE)</td>
    </tr>
    <tr>
      <td bgcolor="#f3f3f3">Public Key<span class="red">※</span></td>
      <td>
        <span class="red"><!--{$arrErr.livePublicKey}--></span>
        <input type="text" name="livePublicKey" style="width:260px" value="<!--{$arrForm.livePublicKey}-->" placeholder="pkey_xxxxxxxxxxxxxxxxxxx" />
      </td>
    </tr>
    <tr>
      <td bgcolor="#f3f3f3">Secret Key<span class="red">※</span></td>
      <td>
        <span class="red"><!--{$arrErr.liveSecretKey}--></span>
        <input type="text" name="liveSecretKey" style="width:260px" value="<!--{$arrForm.liveSecretKey}-->" placeholder="skey_xxxxxxxxxxxxxxxxxxx" />
      </td>
    </tr>
  </table>

  <table border="0" cellspacing="1" cellpadding="8" summary=" ">
    <tr>
      <td colspan="2" width="90" bgcolor="#f3f3f3">Omise API keys (TEST)</td>
    </tr>
    <tr>
      <td bgcolor="#f3f3f3">Public Key<span class="red">※</span></td>
      <td>
        <span class="red"><!--{$arrErr.testPublicKey}--></span>
        <input type="text" name="testPublicKey" style="width:260px" value="<!--{$arrForm.testPublicKey}-->" placeholder="pkey_xxxxxxxxxxxxxxxxxxx" />
      </td>
    </tr>
    <tr>
      <td bgcolor="#f3f3f3">Secret Key<span class="red">※</span></td>
      <td>
        <span class="red"><!--{$arrErr.testSecretKey}--></span>
        <input type="text" name="testSecretKey" style="width:260px" value="<!--{$arrForm.testSecretKey}-->" placeholder="skey_xxxxxxxxxxxxxxxxxxx" />
      </td>
    </tr>
  </table>

  <table border="0" cellspacing="1" cellpadding="8" summary=" ">
    <tr>
      <td colspan="2" width="90" bgcolor="#f3f3f3">Omise API 設定</td>
    </tr>
    <tr>
      <td bgcolor="#f3f3f3">サンドボックス<span class="red">※</span></td>
      <td>
        <ul>
          <li>
            <label>
              <input type="radio" name="sandbox" value="1" <!--{if $arrForm.sandbox == 1 }-->checked="checked"<!--{/if}--> />
              Omise APIをテストモードで実行する。
            </label>
          </li>
          <li>
            <label>
              <input type="radio" name="sandbox" value="0" <!--{if $arrForm.sandbox == 0 }-->checked="checked"<!--{/if}--> />
              Omise APIを<span style="color: red;">ライブモード (実際に課金)</span> で実行する。
            </label>
          </li>
        </ul>

      </td>
    </tr>
    <tr>
      <td bgcolor="#f3f3f3">キャプチャ<span class="red">※</span></td>
      <td>
        <span class="attention"><!--{$arrErr.autocapture}--></span>
        <ul>
          <li>
            <label>
              <input type="radio" name="autocapture" value="1" <!--{if $arrForm.autocapture == 1 }-->checked="checked"<!--{/if}--> />
              購入時に即時、実売上化する。
            </label>
          </li>
          <li>
            <label>
              <input type="radio" name="autocapture" value="0" <!--{if $arrForm.autocapture == 0 }-->checked="checked"<!--{/if}--> />
              購入時に仮売上(オーソリ)を作成し、受注管理画面から実売上化する。
            </label>
          </li>
        </ul>
      </td>
    </tr>
  </table>

  <div class="btn-area">
    <ul>
      <li>
        <a class="btn-action" href="javascript:;" onclick="document.form1.submit();return false;"><span class="btn-next">登録する</span></a>
      </li>
    </ul>
  </div>

</form>
<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_footer.tpl"}-->
