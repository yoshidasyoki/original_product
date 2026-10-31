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
#### ログイン

URLパス：`api/login`  
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

## 5. UI・UX設計
※ デザインの参考になりそうなサイト  
[keeps](https://keeps.homes/)  
[BelleClub](https://www.belleclub.jp/)  
[pal-system](https://www.pal-system.co.jp/koeteku/okome/)  
[ソコスト](https://soco-st.com/category/money) 
