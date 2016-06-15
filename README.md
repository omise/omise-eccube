# OmiseExt EC-CUBE 2.13.3 plugin

[EC-CUBE](http://www.ec-cube.net)用のOmiseの決済モジュールを追加するプラグインです。

このプラグインはEC-CUBE上でのクレジット決済をOmiseで行う機能を提供します。
Omiseは決済（仮売上、実売上、返金）の機能を提供し、商品や受注はEC-CUBEで独立して管理する仕様です。

購入者によるクレジットカード情報の入力、課金の作成（仮売上 or 実売上）、課金の実売上化、課金の返金のみを管理し、それ以外の機能は持ちません。

Omiseで行った決済には、ECCUBEのorder_idをCharge APIのdescriptionへ保存するため、[ダッシュボード](https://dashboard.omise.co/) のグローバルサーチで `order_id: 6` などのテキストで検索することが可能です。  
※ `order_id: 6` は、dtb_orderテーブルで管理している、order_idのことです。



## 機能

- [Omise](https://dashboard.omise.co/signup)にメールアドレスとパスワードを登録するだけですぐ試せます
- Omiseでは、トークン機能を用いるため EC-CUBE のサーバでカード情報を一切扱いません
- EC-CUBE の登録ユーザと Omise の顧客を紐付けることにより、ログインしていれば次回以降はカード情報を入力せず利用できます
- EC-CUBE 内の注文、ユーザデータと Omise の課金、顧客データを ID で対応づけて管理できます
- カード情報は Omise のサーバで[安全](https://www.omise.co/security)に管理されます。 EC-CUBE のサーバで厳しいカード情報の保存要件を満たす必要はありません
- プラグインの設定 サンドボックスで、APIのテストキーかライブキーを利用するかを選択できます。
- プラグインの設定 キャプチャで、購入時に仮売上か実売上化するかを選択できます。


## 注意事項

- EC-CUBE 2.13.3 での動作を保証します。
- PHP 5.3 以上でのみ動作します。PHP 5.2 はすでにサポートが終了しており、安全でないためサポートしません。
- トークン決済に JavaScript が必要なため、携帯電話端末では利用できません。
- [omise-php v2.4.1](https://github.com/omise/omise-php/releases/tag/v2.4.1)を当プラグインで利用しています。

## インストール方法

こちらから [omise-eccube.tar.gz](https://github.com/omise/omise-eccube/blob/master/release/omise-eccube.tar.gz?raw=true) をダウンロードします。

EC-CUBEの管理画面でオーナーズストアのプラグイン管理を開き、先ほど作成した `omise-eccube.tar.gz` を選択し、インストールボタンをクリックします。

![screen shot 2016-04-20 at 11 33 19](https://cloud.githubusercontent.com/assets/5040538/14663495/ba6ecc6c-06eb-11e6-8bfb-c5148951c901.png)


インンストール後、プラグインを有効にするため、チェックボックスをONにします。

![screen shot 2016-04-20 at 12 08 26](https://cloud.githubusercontent.com/assets/5040538/14663976/a6e1b524-06f0-11e6-94e8-01d8c8744a1f.png)

その後、 `プラグイン設定` をクリックし、Omise決済プラグイン設定を開きます。

![screen shot 2016-04-20 at 12 08 34](https://cloud.githubusercontent.com/assets/5040538/14663975/a6e15cb4-06f0-11e6-9454-33f9c27cfea6.png)

この画面では、下記の設定をしてください。

**Omise API keys ( LIVE )**

[LIVE Keys](https://dashboard.omise.co/live/api-keys) より、キーセットを確認し、設定してください。
LIVEダッシュボードがアクティブでない場合は、TESTのキーセットを入力してください。
LIVEダッシュボードは、必要書類をウェブから提出後に利用できるようになります。

**Omise API keys ( TEST )**

[TEST Keys](https://dashboard.omise.co/test/api-keys) より、キーセットを確認し、設定してください。

**サンドボックス**

テスト環境での、プラグインの動作確認の場合は、テストモードでご利用ください。
本番環境で利用される場合は、ライブモードを選択してください。

**キャプチャ**

購入時に仮売上をするメリットは、管理者が在庫確認後、商品の提供が間違いなく行えると認識した段階で、受注画面から手動で実売上にすることができることです。  
また、仮売上状態で7日以上経過すると自動的にオーソリがキャンセル扱いになるため、決済手数料が発生しません。  
実売上後の返金は、決済手数料が発生します。  
ご利用に最適な方法を選択してください。

*Omiseクレジット決済の予備知識*

| 状態 | 内容 |
|:--|:--|
| 仮売上から7日経過してvoidされた | 誰も損しない |
| 実売上して返金した | 加盟店が決済手数料分を損する |


**支払い方法の名称変更**

当プラグインでは、支払い方法のデフォルト名称を `クレジットカード決済[Omise]` と設定しています。 クレジットカード決済は、幾つかのモジュールでも提供されているため、初期値は判別のつくようにしています。

![screen shot 2016-04-20 at 12 26 09](https://cloud.githubusercontent.com/assets/5040538/14664227/17c44be2-06f3-11e6-8bf1-32745136320c.png)

支払い方法の名称は、 `基本情報管理＞支払い方法設定` より、 `クレジットカード決済[Omise]` -> `クレジットカード決済` へ名称を編集することをお勧めします。
これは、ユーザーが購入時の支払い方法で表示されるためです。


**配送方法設定から、取扱支払方法にクレジットカード決済を追加**

最後に、Omiseのクレジットカード決済を有効にしたい配送方法を開き、 `取扱支払方法` でチェックボックスをONにしてください。

![screen shot 2016-04-20 at 12 33 46](https://cloud.githubusercontent.com/assets/5040538/14664320/2c017098-06f4-11e6-953c-cdc577ea6abc.png)

![screen shot 2016-04-20 at 12 33 53](https://cloud.githubusercontent.com/assets/5040538/14664321/2c01b38c-06f4-11e6-84f9-7f063213aff5.png)

これで、選択された配送方法の商品の購入時に、Omiseのクレジット決済が利用できるようになります。


## アップデート方法

アップデートの提供は予定していません。  


## 利用方法

- 試しになにかの商品を購入し、支払方法に「クレジットカード決済」を指定すると、カード情報を入力する画面に遷移します。
![screen shot 2016-04-21 at 14 18 56](https://cloud.githubusercontent.com/assets/5040538/14700803/0870d0dc-07cc-11e6-81f8-3b49a7089bd1.png)

- クレジットカード情報を入力し、購入できることを確認してください。
![screen shot 2016-04-21 at 14 19 02](https://cloud.githubusercontent.com/assets/5040538/14700804/0871ae4e-07cc-11e6-9e92-10d601a0c3a5.png)

- [Omiseのテストダッシュボード](https://dashboard.omise.co/test/charges)で、正しく課金が作成されたことを確認してください。
![screen shot 2016-04-21 at 14 20 59](https://cloud.githubusercontent.com/assets/5040538/14700861/4d03740c-07cc-11e6-9dfb-d8292dfe555f.png)

- 仮売上を利用する設定にした場合、受注詳細画面から実売上化できることを確認してください。
![screen shot 2016-04-21 at 14 14 20](https://cloud.githubusercontent.com/assets/5040538/14700681/66594388-07cb-11e6-9f65-c0797fce1611.png)

- 返金をしたい場合、受注詳細画面から返金ボタンをクリックして、返金できることを確認してください。
![screen shot 2016-04-21 at 14 16 11](https://cloud.githubusercontent.com/assets/5040538/14700721/a0d68912-07cb-11e6-99f0-74b82f4c7455.png)


## ログ

このプラグインは、Omiseとの通信内容を一切保存しません。
Omiseのログを参照するには、ダッシュボードへアクセスしてください。

- [テスト・ログ](https://dashboard.omise.co/test/logs)
- [ライブ・ログ](https://dashboard.omise.co/live/logs)

当プラグインは、設計上、購入者の個人情報、クレジットカード情報、EC-CUBE の設定情報がログに出力されることはありません。
スクリプトを変更する際はこの点が維持されるよう注意してください。


# ライセンス

このリポジトリに含まれるプログラムはOmiseが権利を有しており、
MIT License のもとで配布されます。
