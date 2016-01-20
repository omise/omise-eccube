<!--{if $arrForm.plg_OmisePaymentGateway_enabled == true}-->
<h2>OmisePaymentGateway決済情報</h2>
<table class="form">
	<tbody>
		<tr>
			<th>決済ID</th>
			<td><!--{$arrForm.plg_OmisePaymentGateway_charge}--></td>
		</tr>
		<tr>
			<th>ステータス</th>
			<td><!--{$arrForm.plg_OmisePaymentGateway_status}--></td>
		</tr>
		<tr>
			<th>金額</th>
			<td><!--{$arrForm.plg_OmisePaymentGateway_amount}--><!--{$arrForm.plg_OmisePaymentGateway_amount_warning}--></td>
		</tr>
		<tr>
			<th>決済発行日</th>
			<td><!--{$arrForm.plg_OmisePaymentGateway_create_date}--></td>
		</tr>
		<tr>
			<th>処理</th>
			<td><input type="button" value="売上確定"/><input type="button" value="決済金額変更"/></td>
		</tr>
	</tbody>
</table>
<!--{/if}-->
