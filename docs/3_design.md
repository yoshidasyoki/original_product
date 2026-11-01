# 設計
このセクションでは以下の項目について検討を行い、開発アプリの設計を行う。

1. 画面遷移図・ワイヤフレーム
2. DB設計・テーブル定義書
3. システム設計
4. API設計
5. UI・UX設計（アプリデザイン） 

## 1. 画面遷移図・ワイヤフレーム
Figmaを用いて実施。  
プロトタイプモードで各画面画面への遷移を記述

[Figma 画面遷移図](https://www.figma.com/design/Zyym1C7jcZUdOiFpux4ScC/NISA%E3%82%A2%E3%83%97%E3%83%AA?node-id=37-124&t=y3eO2zcC6E7Z1OXm-1)  

※ 使用予定の外部リンク
#### ステップ１：積立投資とNISAの基礎
- [ソニー生命 積立投資とは？](https://www.sonylife.co.jp/media/manavi/51/)  
- [金融庁 資産形成の基本](https://www.fsa.go.jp/policy/nisa2/invest/)  

#### ステップ３：投資シミュレーション
- [金融庁 NISAの活用事例](https://www.fsa.go.jp/policy/nisa2/example/)
- [金融庁 ライフプランシミュレーター](https://www.fsa.go.jp/policy/nisa2/lifeplan-simulator/)
- [金融庁 つみたてシミュレーター](https://www.fsa.go.jp/policy/nisa2/tsumitate-simulator/)

## 2. DB設計・テーブル定義書
### 論理設計
<img width="485" height="302" alt="image" src="https://github.com/user-attachments/assets/e4adc210-5285-4bf1-b1b4-96a9c324353d" />

### 物理設計
<img width="506" height="302" alt="image" src="https://github.com/user-attachments/assets/fb1a9d5b-5129-4504-9e7b-643d74754336" />

### テーブル設計

#### 1. usersテーブル
| カラム名        | データ型     | NULL | キー    | 初期値             | AUTO INCREMENT |
| ------------     | ------------ | ---- | ------- | ----------------- | -------------- |
| id               | INTEGER      |      | PRIMARY |                   | YES            |
| name             | VARCHAR(50)  |      |         |                   |                |
| email            | VARCHAR(100) |      |         |                   |                |
| password         | VARCHAR(100) |      |         |                   |                |

#### 2. user_curriculum_progressesテーブル
| カラム名        | データ型     | NULL | キー    | 初期値             | AUTO INCREMENT |
| ------------     | ------------ | ---- | ------- | ----------------- | -------------- |
| id               | INTEGER      |      | PRIMARY |                   | YES            |
| user_id          | VARCHAR(50)  |      | FOREIGN |                   |                |
| content_name     | VARCHAR(100) |      |         |                   |                |
| status           | VARCHAR(30)  |      |         | not_started       |                |

※ 外部キー制約について
1. `user_id`は`users`テーブル`id`と外部キーの関係にある
2. `users`テーブルの特定ユーザで削除操作が行われた場合には本テーブルの該当ユーザ情報についても削除する  
（`ON DELETE CASCADE`で外部キー制約を設定する）

#### 3. household_ledgersテーブル
| カラム名              | データ型     | NULL | キー    | 初期値             | AUTO INCREMENT |
| --------------------- | ------------ | ---- | ------- | ----------------- | -------------- |
| id                    | INTEGER      |      | PRIMARY |                   | YES            |
| user_id               | VARCHAR(50)  |      | FOREIGN |                   |                |
| household_category_id | INTEGER      |      | FOREIGN |                   |                |
| record_date           | DATETIME     |      |         |                   |                |
| memo                  | VARCHAR(50)  | YES  |         |                   |                |
| price                 | INTEGER      |      |         | 0                 |                |

※ 外部キー制約について
1. `user_id`は`users`テーブル`id`と外部キーの関係にある
2. `users`テーブルの特定ユーザで削除操作が行われた場合には本テーブルの該当ユーザ情報についても削除する  
（`ON DELETE CASCADE`で外部キー制約を設定する）
3. `household_category_id`は`household_categories`テーブル`id`と外部キーの関係にある
4. 使用されている`household_category_id`がある場合、`household_categories`テーブルの該当idの削除・更新は許可しない  
（`NO ACTION`相当。MySQLのデフォルト設定とする）

#### 4. household_categoriesテーブル
| カラム名        | データ型     | NULL | キー    | 初期値             | AUTO INCREMENT |
| ------------     | ------------ | ---- | ------- | ----------------- | -------------- |
| id               | INTEGER      |      | PRIMARY |                   | YES            |
| name             | VARCHAR(50)  |      |         |                   |                |
| type             | VARCHAR(30)  |      |         |                   |                |

#### 5. investment_productsテーブル
| カラム名        | データ型     | NULL | キー    | 初期値             | AUTO INCREMENT |
| ------------     | ------------ | ---- | ------- | ----------------- | -------------- |
| id               | INTEGER      |      | PRIMARY |                   | YES            |
| name             | VARCHAR(100) |      |         |                   |                |
| type             | VARCHAR(30)  |      |         |                   |                |
| unit             | VARCHAR(30)  |      |         |                   |                |

#### 6. market_pricesテーブル
| カラム名              | データ型       | NULL | キー    | 初期値             | AUTO INCREMENT |
| --------------------- | -------------- | ---- | ------- | ----------------- | -------------- |
| id                    | INTEGER        |      | PRIMARY |                   | YES            |
| investment_product_id | INTEGER        |      | FOREIGN |                   |                |
| price                 | DECIMAL(16, 2) |      |         |                   |                |
| record_date           | DATETIME       |      |         |                   |                |

※ 外部キー制約について
1. `investment_product_id`は`investment_products`テーブル`id`と外部キーの関係にある
2. 使用されている`investment_product_id`がある場合、`investment_products`テーブルの該当idの削除・更新は許可しない  
（`NO ACTION`相当。MySQLのデフォルト設定とする）

## 4. API設計
### ログイン

URLパス：`/api/login`  
HTTPメソッド：`POST`  

以下のようなリクエストJSONを送ると認証を行う。
```JSON
{
  "email": "user@example.com",
  "password": "your_password"
}
```

認証に成功するとステータスコード`200`を返す。  
失敗の場合はステータスコード`401`を返す。

### 家計簿機能
#### 一覧取得
URLパス：`/api/household/transactions`  
HTTPメソッド：`GET`  

リクエストに対して以下のようなJSONを返す。
```JSON
{
  "period": {
    "year": 2026,
    "month": 10
  },
  "summary": {
    "income": 180000,
    "expense": 126600,
    "balance": 53400
  },
  "transactions": [
    {
      "id": 1,
      "date": "2026-10-24",
      "type": "expense",
      "category": "食費",
      "memo": "イオンモール幕張",
      "amount": 5415
    },
    {
      "id": 2,
      "date": "2026-10-20",
      "type": "expense",
      "category": "交際費",
      "memo": "駅前の居酒屋",
      "amount": 3300
    },
    {
      "id": 3,
      "date": "2026-10-08",
      "type": "income",
      "category": "給料",
      "memo": "ボーナス",
      "amount": 60000
    }
  ]
}
```

### 登録
URLパス：`/api/household/transactions`  
HTTPメソッド：`POST`  

以下のような形でリクエストを送る。  
登録に成功するとステータスコード`201`を返す。

```JSON
{
  "date": "2026-10-25",
  "type": "expense",
  "category_id": 1,
  "memo": "スーパーで買い物",
  "amount": 3500
}
```

```JSON
{
  "date": "2026-10-20",
  "type": "income",
  "category_id": 6,
  "memo": "給料",
  "amount": 200000
}
```

### 編集
URLパス：`/api/household/transactions/1`  
HTTPメソッド：`PUT`  

以下のような形でリクエストを送る。  
処理に成功した場合はステータスコード`204`を返す。

```JSON
{
  "date": "2026-10-24",
  "type": "expense",
  "category_id": 1,
  "memo": "イオンモール幕張",
  "amount": 6000
}
```

### 削除
URLパス：`/api/household/transactions/1`  
HTTPメソッド：`DELETE`  

リクエストに対して処理に成功した場合はステータスコード`204`を返す。

## 5. UI・UX設計
※ デザインの参考になりそうなサイト  
[keeps](https://keeps.homes/)  
[BelleClub](https://www.belleclub.jp/)  
[pal-system](https://www.pal-system.co.jp/koeteku/okome/)  
[ソコスト](https://soco-st.com/category/money) 

## その他
### 損失シミュレーションについて
積立投資の仕組みについて少し学習が必要。具体的には以下の内容で調査が必要。

- 総資産の算出方法について
指定した年月間で毎月積立を行った場合の、最終的な資産額の算出の仕方  
→ ここは毎月の購入口数を計算、積立修了日の保有口数と価格をかければOK

- 為替込みのシミュレーションについて
為替相場も考慮したときどのような演算が必要になるかを調べる。  
→ 当時の為替レートからドルに変換したのち口数を算出すればOK

- 複利について
複利演算の方法について調査する。  
→ S&P500（Total Return）という複利込みの指数があるのでこちらを使用すれば演算不要。  
[yahoo finance S&P500（Total Return）](https://finance.yahoo.com/quote/%5ESP500TR/?guccounter=1&guce_referrer=aHR0cHM6Ly93d3cuZ29vZ2xlLmNvbS8&guce_referrer_sig=AQAAAB2RRD5xKrm5wDC1MGdsMI5UBxy1tBw1SGzFacAaooVYQM_L5zj7SaYBRRSmc09d7P3TQwjdP_8nG3faoC4cx4c19lgGTKIHZIBPfHA9p4mWCYyp9hFr-zeKXZduE7fdVV0vrgOaDLs7N3RjJQoAcX3YR6k2W4A2PXrjPaLO9-5r)
