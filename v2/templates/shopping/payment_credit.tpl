<div id="plg_omisepaymentgateway_credit" style="display:none;">
	<p class="attention" id="plg_OmisePaymentGateway_err"></p>
	<input type="hidden" id="plg_OmisePaymentGateway_token" name="plg_OmisePaymentGateway_token" />
	<table>
		<tbody>
			<tr>
				<td bgcolor="#f3f3f3">カード番号<span style="color:red;">※</span></td>
				<td><input type="text" id="plg_OmisePaymentGateway_credit_number" style="width:300px">
				
				<br/>例）0123456789012345
				<br/>※ハイフンは入力してないでください。</td>
			</tr>
			<tr>
				<td bgcolor="#f3f3f3">カード名義人<span style="color:red;">※</span></td>
				<td><input type="text" id="plg_OmisePaymentGateway_name" style="width:300px"><br/>例）TARO SUZUKI</td>
			</tr>
			<tr>
				<td bgcolor="#f3f3f3">有効期限<span style="color:red;">※</span></td>
				<td>
					<select id="plg_OmisePaymentGateway_expiration_year">
						<!--{foreach from=$arrForm.plg_OmisePaymentGateway_expiration_years item=value}-->
							<option value="<!--{$value}-->"><!--{$value}--></option>
						<!--{/foreach}-->
					</select>年
					<select id="plg_OmisePaymentGateway_expiration_month" style="margin-left:10px;">
						<!--{foreach from=$arrForm.plg_OmisePaymentGateway_expiration_months item=value}-->
							<option value="<!--{$value}-->"><!--{$value}--></option>
						<!--{/foreach}-->
					</select>月
				</td>
			</tr>
			
			<tr>
				<td bgcolor="#f3f3f3">セキュリティコード<span style="color:red;">※</span></td>
				<td><input type="text" id="plg_OmisePaymentGateway_security_code" style="width:60px"><br/>例）456<br/>セキュリティコードとは、カード裏面に印刷されている3桁から4桁の数字のことです。</td>
			</tr>
		</tbody>
	</table>
</div>
<script src="https://cdn2.omise.co/omise.js.gz"></script>
<script type="text/javascript">
function plg_OmisePaymentGateway_form_init() {
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
}

function plg_OmisePaymentGateway_validation(number, name, expiration_month, expiration_year, security_code) {
	var error = '';
	
	if(number.match(/[^0-9]+/)) error += 'カード番号は半角数字で入力してください。</br>';
	if(number.length !== 16) error += 'カード番号は16桁です。</br>';
	if(name.length < 1) error += 'カード名義人を入力してください。</br>';
	if(security_code.match(/[^0-9]+/)) error += 'セキュリティコードは半角数字で入力してください。</br>';
	if(expiration_year.match(/[^0-9]+/)) error += '有効期限（年）は半角数字で入力してください。</br>';
	if(expiration_month.match(/[^0-9]+/)) error += '有効期限（月）は半角数字で入力してください。</br>';
	var len = security_code.length;
	if(len !== 3 && len !== 4) error += 'セキュリティコードは3〜4桁です。</br>';

	return error;
}

function plg_OmisePaymentGateway_show_error(error_msg) {
	$('#plg_OmisePaymentGateway_err').html(error_msg);
	$('body,html').animate({scrollTop:$('#plg_OmisePaymentGateway_err').offset().top}, 400, 'swing');
}

$(function() {
	// フォームの初期化
	plg_OmisePaymentGateway_form_init();

	// Omiseキーの設定
	Omise.setPublicKey('<!--{$arrForm.plg_OmisePaymentGateway_pkey}-->');
	
	// submit時にOmiseAPIからTokenを取得する
	$('#form1').submit(function() {$("[name='radio_group']:checked")
		if($("input:radio[name='payment_id']:checked").val() == <!--{$arrForm.plg_OmisePaymentGateway_payment_id}-->) {
			if($('#plg_OmisePaymentGateway_token').val().length > 0) return true;
			
			var number = $('#plg_OmisePaymentGateway_credit_number').val();
			var name = $('#plg_OmisePaymentGateway_name').val();
			var expiration_month = $('#plg_OmisePaymentGateway_expiration_month').val();
			var expiration_year = $('#plg_OmisePaymentGateway_expiration_year').val();
			var security_code = $('#plg_OmisePaymentGateway_security_code').val();

			var error = plg_OmisePaymentGateway_validation(number, name, expiration_month, expiration_year, security_code);
			if(error.length === 0) {
				var card = {
				  "name": name,
				  "number": number,
				  "expiration_month": expiration_month,
				  "expiration_year": expiration_year,
				  "security_code": security_code
				};
		
				Omise.createToken("card", card, function (statusCode, response) {
					if (statusCode == 200) {
						if(response.id != undefined && response.card.security_code_check == true) {
							$('#plg_OmisePaymentGateway_token').val(response.id);
							$('#form1').submit();
						} else {
							plg_OmisePaymentGateway_show_error('無効なカードです。<br/>別のカードを入力するか、入力内容をお確かめください。');
						}
					} else {
						plg_OmisePaymentGateway_show_error('無効なカードです。<br/>別のカードを入力するか、入力内容をお確かめください。');
					};
				});
			} else {
				plg_OmisePaymentGateway_show_error(error);
			}
			
			return false;
		} else {
			return true;
		}
	});

	$(function() {
		// 支払情報取得のajax完了時にクレジット入力欄をリセットする
	    $(document).ajaxComplete(function(event, xhr, settings) {
		    if(xhr.readyState == 4 && xhr.status == 200) {
			    if('arrPayment' in xhr.responseJSON) {
			    	plg_OmisePaymentGateway_form_init();
			    }
		    }
		})
	});
});
</script>
