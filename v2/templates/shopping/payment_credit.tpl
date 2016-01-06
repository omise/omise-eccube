<div id="plg_omisepaymentgateway_credit">
	<table>
		<tbody>
			<tr>
				<td bgcolor="#f3f3f3">カード番号<span style="color:red;">※</span></td>
				<td><input type="text" name="plg_OmisePaymentGateway_credit_number1" style="width:60px"> - <!-- 
				 --><input type="text" name="plg_OmisePaymentGateway_credit_number2" style="width:60px"> - <!-- 
				 --><input type="text" name="plg_OmisePaymentGateway_credit_number3" style="width:60px"> - <!-- 
				 --><input type="text" name="plg_OmisePaymentGateway_credit_number4" style="width:60px">
				
				<br/>例）0123-4567-8901-2345</td>
			</tr>
			<tr>
				<td bgcolor="#f3f3f3">カード名義人<span style="color:red;">※</span></td>
				<td><input type="text" name="plg_OmisePaymentGateway_name" style="width:300px"><br/>例）TARO SUZUKI</td>
			</tr>
			<tr>
				<td bgcolor="#f3f3f3">有効期限<span style="color:red;">※</span></td>
				<td>
					<select name="plg_OmisePaymentGateway_expiration_year">
						<!--{foreach from=$arrForm.plg_OmisePaymentGateway_expiration_years item=value}-->
							<option value="<!--{$value}-->"><!--{$value}--></option>
						<!--{/foreach}-->
					</select>年
					<select name="plg_OmisePaymentGateway_expiration_month" style="margin-left:10px;">
						<!--{foreach from=$arrForm.plg_OmisePaymentGateway_expiration_months item=value}-->
							<option value="<!--{$value}-->"><!--{$value}--></option>
						<!--{/foreach}-->
					</select>月
				</td>
			</tr>
			
			<tr>
				<td bgcolor="#f3f3f3">セキュリティコード<span style="color:red;">※</span></td>
				<td><input type="text" name="plg_OmisePaymentGateway_security_code" style="width:60px"><br/>例）456<br/>セキュリティコードとは、カード裏面に印刷されている3桁から4桁の数字のことです。</td>
			</tr>
		</tbody>
	</table>
</div>
<script src="https://cdn2.omise.co/omise.js.gz"></script>
<script type="text/javascript">
$(function() {
	// クレジットカード選択時の挙動
	var rdo_payments = document.getElementsByName('payment_id');
	var rdo_pay_len = rdo_payments.length;
	
	for(i = 0; i < rdo_pay_len; ++i) {
		rdo_payments[i].onchange = function() {
			if(this.value == <!--{$arrForm.plg_OmisePaymentGateway_payment_id}--> && this.checked) {
				document.getElementById('plg_omisepaymentgateway_credit').style.display = "block";
			} else {
				document.getElementById('plg_omisepaymentgateway_credit').style.display = "none";
			}
		};

		if(rdo_payments[i].value == <!--{$arrForm.plg_OmisePaymentGateway_payment_id}-->) {
			if(rdo_payments[i].checked) {
				document.getElementById('plg_omisepaymentgateway_credit').style.display = "block";
			} else {
				document.getElementById('plg_omisepaymentgateway_credit').style.display = "none";
			}
		}
	}

	// Omiseキーの設定
	Omise.setPublicKey('<!--{$arrForm.plg_OmisePaymentGateway_pkey}-->');
});
</script>
