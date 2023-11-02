<!--{*
/**
 * This file is part of EC-CUBE Plugin OmiseExt
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */
*}-->

<script type="text/javascript">
<!--
function fnPlgOmiseExtCapture() {
  if(window.confirm('現在の金額で実売上化をおこないます。受注内容を変更中の場合はさきに保存してください。')) {
    eccube.setModeAndSubmit('plg_omiseext_capture', '', '');
  };
  return false;
}

function fnPlgOmiseExtRefund() {
  if(window.confirm('実売上化した金額を返金します。返金後は、この受注データに再度の課金を行うことはできませんのでご注意ください。')) {
    eccube.setModeAndSubmit('plg_omiseext_refund', '', '');
  };
  return false;
}
//-->
</script>

<h2 id="plg_omiseext_detail">Omise課金情報</h2>

<table class="form">
    <!--{if $plg_omiseext_objCharge}-->
    <tr>
        <th>課金詳細ページ</th>
        <td><a href="<!--{$plg_omiseext_objCharge->getOmisePage()}-->" target="_blank"><!--{$plg_omiseext_objCharge->getChargeId()}--></a></td>
    </tr>
    <tr>
        <th>状態</th>
        <td>
            <!--{if $plg_omiseext_capture_error}-->
                <span class="attention"><!--{$plg_omiseext_capture_error}--></span>
            <!--{/if}-->
            <!--{if $plg_omiseext_refund_error}-->
                <span class="attention"><!--{$plg_omiseext_refund_error}--></span>
            <!--{/if}-->

            <!--{if $plg_omiseext_objCharge->isRefunded() }-->
              <!--{$plg_omiseext_objCharge->getRefunded()|default:0|number_format}-->円の返金済み<br>
              <span class="attention">※ 返金済みのため、この受注データでは再度のOmiseを使ったクレジットカード決済はできません。</span>
            <!--{elseif $plg_omiseext_objCharge->isCaptured() }-->
                <!--{$plg_omiseext_objCharge->getAmount()|default:0|number_format}-->円の実売上
                <a class="btn-normal" href="javascript:;"
                   onclick="fnPlgOmiseExtRefund()">返金する</a>
                   <br>
                   <span class="attention">※ 返金すると、この受注データに対して、Omiseのクレジットカード決済で再度のチャージをすることはできません。</span>
            <!--{else}-->
                <!--{$plg_omiseext_objCharge->getAmount()|default:0|number_format}-->円の仮売上
                <a class="btn-normal" href="javascript:;"
                   onclick="fnPlgOmiseExtCapture()">実売上化する</a>

            <!--{/if}-->
        </td>
    </tr>
    <!--{else}-->
    <tr>
      <td>課金は未作成です</td>
    </tr>
    <!--{/if}-->
</table>
