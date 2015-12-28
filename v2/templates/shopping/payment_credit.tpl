<div id="plg_omisepaymentgateway_credit">
	<table>
		<tbody>
			<tr>
				<td bgcolor="#f3f3f3">カード番号<span style="color:red;">※</span></td>
				<td><input type="text" name="omise_credit_number" style="width:300px"><br/>例）0123456789012345<br/>ハイフンは入力しないでください。</td>
			</tr>
			<tr>
				<td bgcolor="#f3f3f3">氏名<span style="color:red;">※</span></td>
				<td><input type="text" name="omise_name" style="width:300px"><br/>例）TARO SUZUKI</td>
			</tr>
			<tr>
				<td bgcolor="#f3f3f3">有効期限<span style="color:red;">※</span></td>
				<td>
					<select name="omise_expiration_year">
						<?php 
						$y = date('Y');
						$ey = $y + 10;
						while($y <= $ey) {
							echo '<option value="', $y, '">', $y, '</option>';
							++$y;
						}
						?>
					</select>年
					<select name="omise_expiration_month" style="margin-left:10px;">
						<option value="01">01</option>
						<option value="02">02</option>
						<option value="03">03</option>
						<option value="04">04</option>
						<option value="05">05</option>
						<option value="06">06</option>
						<option value="07">07</option>
						<option value="08">08</option>
						<option value="09">09</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
					</select>月
				</td>
			</tr>
			<tr>
				<td bgcolor="#f3f3f3">セキュリティコード<span style="color:red;">※</span></td>
				<td><input type="text" name="omise_security_code" style="width:60px"><br/>例）456<br/>セキュリティコードとは、カード裏面に印刷されている3桁から4桁の数字のことです。</td>
			</tr>
		</tbody>
	</table>
</div>
<script type="text/javascript">
$(function() {
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
});
</script>
