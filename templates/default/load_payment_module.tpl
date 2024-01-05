<!--{*
/**
 * This file is part of EC-CUBE Plugin OmiseExt
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */
*}-->

<div id="undercolumn">
  <div id="undercolumn_shopping">
    <p class="flow_area"><img src="<!--{$TPL_URLPATH}-->img/picture/img_flow_03.jpg" alt="購入手続きの流れ" /></p>
    <h2 class="title"><!--{$tpl_title|h}--></h2>


    <!--{if gettype($arrErr) == "array" && count($arrErr) > 0}-->
      <p class="remark attention omise-notice"><!--{foreach from=$arrErr item=errorMessage}-->
      <!--{$errorMessage}-->
      <!--{/foreach}--></p>
    <!--{/if}-->
    <!--{if $tpl_omise_charge_error }-->
      <p class="remark attention omise-notice"><!--{$tpl_omise_charge_error}--></p>
    <!--{/if}-->

    <form name="form1" id="form1" method="POST" action="<!--{$tpl_url}-->" autocomplete="on">
      <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
      <input type="hidden" name="mode" value="pay" />

      <div id="token_errors"></div>
      <input type="hidden" name="omise_token">

      <!--{if $objSavedCard !== null}-->
        次のカードが登録されています。
        <table summary="登録ずみカード情報">
          <tr>
            <th>カードブランド</th>
            <td><!--{$objSavedCard.brand}--></td>
          </tr>
          <tr>
            <th>カード番号</th>
            <td>****-****-****-<!--{$objSavedCard.last_digits}--></td>
          </tr>
          <tr>
            <th>名義人</th>
            <td><!--{$objSavedCard.name}--></td>
          </tr>
          <tr>
            <th>有効期限(月/年)</th>
            <td><!--{$objSavedCard.expiration_month|string_format:"%02d"}-->/<!--{$objSavedCard.expiration_year|string_format:"%04d"}--></td>
          </tr>
        </table>
        <input type="hidden" name="card_info" value="" />
        <div class="btn_area">
          <ul>
            <li>
              <a class="omise-button-list" href="javascript:;" onclick="eccube.fnFormModeSubmit('form2', 'pay', 'card_info', 'customer'); return false;"><span class="btn-next">登録してあるカードで支払う</span></a>
            </li>
            <li>
              <a class="omise-button-list" href="javascript:;" onclick="eccube.fnFormModeSubmit('form2', 'delete_card'); return false;"><span class="btn-next">登録したカードを削除する</span></a>
            </li>
            <li>
              <a class="omise-button-list" href="javascript:;" onclick="eccube.fnFormModeSubmit('form2', 'other_card'); return false;"><span class="btn-next">別のカードで支払う</span></a>
            </li>
          </ul>
        </div>
      <!--{else}-->
        <input type="hidden" name="card_info" value="token" />
        <table id="omise_tbl">
          <tbody>
            <tr>
              <th>カード番号<span style="color:red;">※</span></th>
              <td><input type="text" data-omise="number" placeholder="1234567809123456" maxlength="19">
              <br>例）1234567809123456
              <br>※ハイフンは入力してないでください。</td>
            </tr>
            <tr>
              <th>カード名義人<span style="color:red;">※</span></th>
              <td><input type="text" data-omise="holder_name" placeholder="TARO YAMADA"><br>例）TARO YAMADA</td>
            </tr>
            <tr>
              <th>有効期限<span style="color:red;">※</span></th>
              <td>
                <input type="text" data-omise="expiration_month" size="4" placeholder="MM" maxlength="2">月
                <input type="text" data-omise="expiration_year" size="8" placeholder="YYYY" maxlength="4">年
                <br>例）02月 / <!--{php}-->echo intval(date('Y') + 4);<!--{/php}-->年
              </td>
            </tr>
            <tr>
              <th>セキュリティコード<span style="color:red;">※</span></th>
              <td><input type="text" data-omise="security_code" size="8" maxlength="4" placeholder="•••"><br>例）456<br>セキュリティコードとは、カード裏面に印刷されている3桁から4桁の数字のことです。</td>
            </tr>
          </tbody>
        </table>
        <!--{if $tpl_is_registered_customer === true}-->
          <div class="omise-card-save-cb">
            <label>
              <input type="checkbox" name="card_info" value="customer_from_token" checked="checked" />
              今回利用したカード情報を安全に保存し、次回からカード情報入力なしで利用する
            </label>
          </div>
        <!--{/if}-->
        <div>
          <input class="omise-button" type="submit" id="create_token" value="クレジットカードで支払う">
        </div>

      <!--{/if}-->
    </form>
    <!--{if $objSavedCard !== null}-->
      <form name="form2" id="form2" method="POST" action="<!--{$tpl_url}-->">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <input type="hidden" name="mode" value="pay" />
        <input type="hidden" name="omise_token">
        <input type="hidden" name="card_info" value="" />
      </form>
    <!--{/if}-->
  </div>
</div>


<script src="https://cdn.omise.co/omise.js"></script>
<script type="text/javascript">
$(function(){
  // Set public key
  Omise.setPublicKey("<!--{$smarty.const.OMISE_PUBLIC_KEY}-->");

  // Hook on submiting form#form1
  $("#form1").submit(function () {
    var form = $(this);
    // Disable the submit button to avoid repeated click.
    form.find("input[type=submit]").prop("disabled", true);

    // Serialize the form fields into a valid card object.
    var card = {
      "name": form.find("[data-omise=holder_name]").val(),
      "number": form.find("[data-omise=number]").val(),
      "expiration_month": form.find("[data-omise=expiration_month]").val(),
      "expiration_year": form.find("[data-omise=expiration_year]").val(),
      "security_code": form.find("[data-omise=security_code]").val()
    };

    // Send a request to create a token then trigger the callback function once
    // a response is received from Omise.
    //
    // Note that the response could be an error and this needs to be handled within
    // the callback.
    Omise.createToken("card", card, function (statusCode, response) {
      if (response.object == "error") {
        // Display an error message.
        $("#token_errors").attr('class', 'omise-notice').html(response.message);

        // Re-enable the submit button.
        form.find("input[type=submit]").prop("disabled", false);
      } else {
        // Then fill the omise_token.
        form.find("[name=omise_token]").val(response.id);

        // Remove card number from form before submiting to server.
        form.find("[data-omise=number]").val("");
        form.find("[data-omise=security_code]").val("");

        // submit token to server.
        form.get(0).submit();
      };
    });

    // Prevent the form from being submitted;
    return false;

  });
});
</script>

<style>
.omise-notice {
  margin: 2em 0;
  padding: 1em 1.5em;
  border: 1px solid #f9d1d1;
  border-radius: 4px;
  background: #fce8e8;
  color: #b66262;
  max-width: 640px;
  font-size: .875em;
}

.omise-button {
  display: block;
}

.omise-button:hover {
  text-decoration: underline;
}

.btn_area {
  padding-top: 2.5em;
}

.omise-button,
.omise-button-list {
  margin: 10px auto;
  padding: 1.5em 2.35em;
  color: #34464c !important;
  border-radius: 4px;
  cursor: pointer;
  border: #c8dae0 1px solid;
  background: #f2f6f7;
  -webkit-transition: background ease-in-out .15s;
  transition: background ease-in-out .15s;
}

.omise-button:hover,
.omise-button-list:hover {
  background: #fff;
}

.omise-card-save-cb {
  margin-bottom: 2em;
}

</style>
