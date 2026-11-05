# 設計

このセクションでは以下の目次項目について検討を行い、開発アプリの設計を行う。

## 目次
1. [画面遷移図・ワイヤフレーム](#1-画面遷移図ワイヤフレーム)
2. [DB設計・テーブル定義書](#2-db設計テーブル定義書)
    - [論理設計](#論理設計)  
    - [物理設計](#物理設計)  
    - [テーブル定義書](#テーブル定義書)  
3. [システム設計](#3-システム設計)
4. [API設計](#4-api設計)
    - [ログイン認証](#ログイン認証)  
    - [アカウント関連](#アカウント関連)  
    - [家計簿](#家計簿)  
    - [シミュレーション](#シミュレーション)  
5. [UI・UX設計](#5-uiux設計)
6. [その他](#6-その他)
    - [積立シミュレーション（過去実績）について](#積立シミュレーション過去実績について) 

## 1. 画面遷移図・ワイヤフレーム
Figmaを用いて実施。  
プロトタイプモードで各画面への遷移を記述

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
<img width="515" height="328" alt="image" src="https://github.com/user-attachments/assets/c5b6fe84-c010-4f4d-9785-ab908582e1ed" />

### 物理設計
<img width="556" height="335" alt="image" src="https://github.com/user-attachments/assets/e127213d-a765-4db2-b93f-b7524ca0cd6e" />

### テーブル定義書
※ 文字コードはすべてMySQL8.4の標準である`utf8mb4`とする

#### 1. usersテーブル
| カラム名        | データ型          | NOT NULL | キー    | 初期値             | AUTO INCREMENT |
| ------------     | ---------------- | ------- | ------- | ----------------- | -------------- |
| id               | BIGINT UNSIGNED  | YES     | PRIMARY |                   | YES            |
| name             | VARCHAR(50)      | YES     |         |                   |                |
| email            | VARCHAR(255)     | YES     | UNIQUE  |                   |                |
| password         | VARCHAR(100)     | YES     |         |                   |                |
| created_at       | TIMESTAMP        | NO      |         |                   |                |
| updated_at       | TIMESTAMP        | NO      |         |                   |                |

※ `password`にはハッシュ値が入る

#### 2. user_learning_progressesテーブル
| カラム名          | データ型          | NOT NULL | キー            | 初期値             | AUTO INCREMENT |
| ------------------ | ---------------- | ------- | ---------------- | ----------------- | -------------- |
| id                 | BIGINT UNSIGNED  | YES     | PRIMARY          |                   | YES            |
| user_id            | BIGINT UNSIGNED  | YES     | FOREIGN, UNIQUE  |                   |                |
| curriculum_step_id | BIGINT UNSIGNED  | YES     | FOREIGN, UNIQUE  |                   |                |
| status             | VARCHAR(30)      | YES     |                  | not_started       |                |
| created_at         | TIMESTAMP        | NO      |                  |                   |                |
| updated_at         | TIMESTAMP        | NO      |                  |                   |                |

※ 一意制約について  
本テーブルでは`user_id`と`curriculum_step_id`を複合一意制約として定義する

※ 外部キー制約について
1. `user_id`は`users.id`を参照する外部キー  
2. `users`テーブルの特定ユーザで削除操作が行われた場合には本テーブルの該当ユーザ情報についても削除する  
（`ON DELETE CASCADE`で外部キー制約を設定する）

#### 3. curriculum_stepsテーブル
| カラム名        | データ型          | NOT NULL | キー          | 初期値             | AUTO INCREMENT |
| ------------     | ---------------- | ------- | -------------- | ----------------- | -------------- |
| id               | BIGINT UNSIGNED  | YES     | PRIMARY        |                   | YES            |
| title            | VARCHAR(100)     | YES     |                |                   |                |
| position         | BIGINT UNSIGNED  | YES     | UNIQUE, CHECK  |                   |                |
| created_at       | TIMESTAMP        | NO      |                |                   |                |
| updated_at       | TIMESTAMP        | NO      |                |                   |                |

※ CHECK制約について  
0より大きい数字のみ保存を許可する

#### 4. household_transactionsテーブル
| カラム名              | データ型         | NOT NULL | キー    | 初期値             | AUTO INCREMENT |
| --------------------- | ---------------- | ------- | ------- | ----------------- | -------------- |
| id                    | BIGINT UNSIGNED　| YES     | PRIMARY |                   | YES            |
| user_id               | BIGINT UNSIGNED  | YES     | FOREIGN |                   |                |
| household_category_id | BIGINT UNSIGNED  | YES     | FOREIGN |                   |                |
| record_date           | DATETIME         | YES     |         |                   |                |
| memo                  | VARCHAR(50)      | NO      |         |                   |                |
| amount                | INTEGER          | YES     | CHECK   |                   |                |
| created_at            | TIMESTAMP        | NO      |         |                   |                |
| updated_at            | TIMESTAMP        | NO      |         |                   |                |

※ CHECK制約について  
0より大きい数字のみ保存を許可する

※ 外部キー制約について  
1. `user_id`は`users.id`を参照する外部キー  
2. `users`テーブルの特定ユーザで削除操作が行われた場合には本テーブルの該当ユーザ情報についても削除する  
（`ON DELETE CASCADE`で外部キー制約を設定する）
3. `household_category_id`は`household_categories.id`を参照する外部キー
4. 使用されている`household_category_id`がある場合、`household_categories`テーブルの該当idの削除・更新は許可しない  
（`NO ACTION`相当。MySQLのデフォルト設定とする）

#### 5. household_categoriesテーブル
| カラム名        | データ型          | NOT NULL | キー        | 初期値             | AUTO INCREMENT |
| ------------     | ---------------- | ------- | ------------ | ----------------- | -------------- |
| id               | BIGINT UNSIGNED  | YES     | PRIMARY      |                   | YES            |
| name             | VARCHAR(50)      | YES     | UNIQUE       |                   |                |
| type             | VARCHAR(30)      | YES     | ENUM, UNIQUE |                   |                |
| icon             | VARCHAR(50)      | YES     |              |                   |                |
| created_at       | TIMESTAMP        | NO      |              |                   |                |
| updated_at       | TIMESTAMP        | NO      |              |                   |                |

※ ENUM制約について  
`income`と`expense`の2値を登録する形とする

※ UNIQUE制約について
`name`と`type`の複合制約として定義する

※ 今回はユーザがカテゴリを追加することは不可能とする

#### 6. investment_productsテーブル
| カラム名        | データ型          | NOT NULL | キー    | 初期値             | AUTO INCREMENT |
| ------------     | ---------------- | ------- | ------- | ----------------- | -------------- |
| id               | BIGINT UNSIGNED  | YES     | PRIMARY |                   | YES            |
| name             | VARCHAR(100)     | YES     |         |                   |                |
| type             | VARCHAR(30)      | YES     |         |                   |                |
| unit             | VARCHAR(30)      | YES     |         |                   |                |
| created_at       | TIMESTAMP        | NO      |         |                   |                |
| updated_at       | TIMESTAMP        | NO      |         |                   |                |

#### 7. market_pricesテーブル
| カラム名              | データ型         | NOT NULL | キー            | 初期値             | AUTO INCREMENT |
| --------------------- | ---------------- | -------- | --------------- | ----------------- | -------------- |
| id                    | BIGINT UNSIGNED  | YES      | PRIMARY         |                   | YES            |
| investment_product_id | BIGINT UNSIGNED  | YES      | FOREIGN, UNIQUE |                   |                |
| value                 | DECIMAL(16, 2)   | YES      |                 |                   |                |
| record_date           | DATE             | YES      | UNIQUE          |                   |                |
| created_at            | TIMESTAMP        | NO       |                 |                   |                |
| updated_at            | TIMESTAMP        | NO       |                 |                   |                |

※ 月初終値を登録するものとする

※ 一意制約について  
`investment_product_id`と`record_date`の複合キーとする

※ 外部キー制約について
1. `investment_product_id`は`investment_products.id`を参照する外部キー
2. 使用されている`investment_product_id`がある場合、`investment_products`テーブルの該当idの削除・更新は許可しない  
（`NO ACTION`相当。MySQLのデフォルト設定とする）

## 3. システム設計
AWSインフラ設計について以下の構成を想定  
- フロント部分はCloudFormationによる実装で検討
- バックエンドはEC2サーバを1台or2台設置予定
- DB部分はRDSを1台導入（コスト面から2台は厳しい）
- ACMを用いてHTTPS化

## 4. API設計
全体のAPI設計は以下の通り。

### ログイン認証
| 項目 | URLパス | HTTPメソッド | 概要 |
| ---- | ---- | ----- | ---- |
| [ログイン](#ログイン) | /api/login | POST | ログイン認証を行う |
| [ログアウト](#ログアウト) | /api/logout | POST | ログアウトを行う |

### アカウント関連
| 項目 | URLパス | HTTPメソッド | 概要 |
| ---- | ---- | ----- | ---- |
| [ユーザ登録](#ユーザ登録) | /api/users  | POST | 新規でユーザ登録を行う
| [ユーザ情報取得](#ユーザ情報取得) | /api/me | GET  | 現在ログインをしているユーザの情報を取得する |
| [ユーザ情報変更](#ユーザ情報変更) | /api/me | PUT  | ログインユーザの名前とメールアドレスを変更する |
| [パスワード変更](#パスワード変更) | /api/me/password | PUT | ログインユーザのパスワードを変更する |
| [学習状況取得](#学習状況取得) | /api/me/learning-progress | GET | ログインユーザの学習の進捗状況を取得する |
| [学習状況編集](#学習状況編集) | /api/me/learning-progress | PUT | ログインユーザの学習状況の編集を行う |

### 家計簿
| 項目 | URLパス | HTTPメソッド | 概要 |
| ---- | ---- | ----- | ---- |
| [家計簿一覧取得](#家計簿一覧取得) | /api/household/transactions | GET | 指定した月のログインユーザの家計簿データを一覧で取得する |
| [収支明細の登録](#家計簿登録) | /api/household/transactions | POST | 家計簿の記録を新規で登録する |
| [収支明細の閲覧](#家計簿閲覧) | /api/household/transactions/1 | GET | 指定の収支明細データを取得する |
| [収支明細の編集](#家計簿編集) | /api/household/transactions/1 | PUT | 指定の収支明細データを編集する |
| [収支明細の削除](#家計簿削除) | /api/household/transactions/1 | DELETE | 指定の収支明細データを削除する |
| [家計簿支出カテゴリ一覧取得](#家計簿支出カテゴリ一覧取得) | /api/household/categories?type=expense | GET | DBに登録されている家計簿の支出カテゴリを取得する |
| [家計簿収入カテゴリ一覧取得](#家計簿収入カテゴリ一覧取得) | /api/household/categories?type=income | GET | DBに登録されている家計簿の収入カテゴリを取得する |

### シミュレーション
| 項目 | URLパス | HTTPメソッド | 概要 |
| ---- | ---- | ----- | ---- |
| [積立シミュレーション（過去実績）](#積立シミュレーション過去実績) | /api/investment/risk-simulations | POST | 暴落込みの値動きを過去の株価データをもと把握する |

### ※ API設計詳細
#### ログイン
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/login |
| HTTPメソッド | POST |
| 概要 | リクエストJSONから認証を行う | 

リクエスト例
```JSON
{
  "email": "user@example.com",
  "password": "your_password"
}
```

レスポンス例（200 OK）
```JSON
{
    "data": {
        "email": "test@example.com",
        "token": "eyJ0eXAiOi..."
    }
}
```

レスポンスについて、  
・認証に成功するとステータスコード`200`を返す。  
・認証失敗の場合はステータスコード`401`を返す。  
・ログイン中に再度ログインリクエストを受けた場合はセッションを再生成してログイン状態を保持する。

#### ログアウト
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/logout |
| HTTPメソッド | POST |
| 概要 | ログアウト処理を行う |

レスポンス例（200 OK）
```JSON
{
  "message": "Logged out successfully"
}
```

※ 未ログイン状態でログアウトのリクエストを受けた場合も上記の成功レスポンスを返すものとする。  
（処理後にログアウト状態にするというこの処理の目的は達成されているため）

#### ユーザ登録
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/users |
| HTTPメソッド | POST |
| 概要 | ユーザの新規登録を行う | 

リクエスト例
```JSON
{
  "name": "testuser",
  "email": "testuser@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

レスポンス例（201 Created）
```JSON
{
  "message": "User registered successfully.",
  "data": {
    "id": 1,
    "name": "testuser",
    "email": "testuser@example.com"
  }
}
```

#### ユーザ情報取得
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/me |
| HTTPメソッド | GET |
| 概要 | 現在ログイン中のユーザ情報を取得する | 

レスポンス例（200 OK）
```JSON
{
  "data": {
    "id": 1,
    "name": "テストユーザ",
    "email": "testuser@mail.com"
  }
}
```

#### ユーザ情報変更
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/me |
| HTTPメソッド | PUT |
| 概要 | ユーザ情報（ユーザ名とメールアドレス）の更新を行う | 

リクエスト例
```JSON
{
  "name": "新しいユーザ名",
  "email": "newuser@mail.com"
  "password": "password"
}
```

レスポンス例（200 OK）
```JSON
{
  "data": {
    "id": 1,
    "name": "新しいユーザ名",
    "email": "newuser@mail.com"
  }
}
```

#### パスワード変更
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/me/password |
| HTTPメソッド | PUT |
| 概要 | 現在のパスワード確認と新パスワードの設定を行う | 

リクエスト例：
```JSON
{
  "current_password": "oldPassword123",
  "password": "newPassword456",
  "password_confirmation": "newPassword456"
}
```

※ 処理に成功した場合はステータスコード`204 No Content`を返す

#### 学習状況取得
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/me/learning-progress |
| HTTPメソッド | GET |
| 概要 | 学習ステータスを取得する | 

レスポンス例（200 OK）：
```JSON
{
  "data": [
    {
      "id": 1,
      "title": "投資とNISAについての基礎を知る",
      "status": "completed"
    },
    {
      "id": 2,
      "title": "投資可能額を把握する",
      "status": "completed"
    },
    {
      "id": 3,
      "title": "シミュレーションで将来の資産を予測",
      "status": "not_started"
    },
    {
      "id": 4,
      "title": "NISA口座開設と商品選択",
      "status": "not_started"
    }
  ]
}
```

#### 学習状況編集
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/me/learning-progress |
| HTTPメソッド | PUT |
| 概要 | 学習ステータスの編集を行う | 

リクエスト例：  
```JSON
{
  steps: [
    {
      "id": 1,
      "status": "completed"
    },
    {
      "id": 2,
      "status": "completed"
    },
    {
      "id": 3,
      "status": "completed"
    },
    {
      "id": 4,
      "status": "not_started"
    }
  ]
}
```

レスポンス例：（200 OK）
```JSON
{
  "data": [
    {
      "id": 1,
      "status": "completed"
    },
    {
      "id": 2,
      "status": "completed"
    },
    {
      "id": 3,
      "status": "completed"
    },
    {
      "id": 4,
      "status": "completed"
    }
  ]
}
```

#### 家計簿一覧取得
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/household/transactions |
| HTTPメソッド | GET |
| 概要 | 指定した月の家計簿データを取得する | 

レスポンス例（200 OK）
```JSON
{
  "data": {
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
}
```

#### 収支明細の登録
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/household/transactions |
| HTTPメソッド | POST |
| 概要 | 収支明細の登録を行う | 

リクエスト例
```JSON
{
  "date": "2026-10-25",
  "type": "expense",
  "category_id": 1,
  "memo": "スーパーで買い物",
  "amount": 3500
}
```

レスポンス例（201 Created）
```JSON
{
  "data": {
    "date": "2026-10-20",
    "type": "income",
    "category_id": 6,
    "memo": "給料",
    "amount": 200000
  }
}
```

#### 収支明細の取得
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/household/transactions/1 |
| HTTPメソッド | GET |
| 概要 | 指定した収支明細データを取得する | 

レスポンス例（200 OK）
```JSON
{
  "data": {
  "date": "2026-10-24",
  "type": "expense",
  "category_id": 1,
  "memo": "イオンモール幕張",
  "amount": 5600
  }
}
```

#### 収支明細の編集
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/household/transactions/1 |
| HTTPメソッド | PUT |
| 概要 | 指定した収支明細データの編集を行う | 

リクエスト例
```JSON
{
  "date": "2026-10-24",
  "type": "expense",
  "category_id": 1,
  "memo": "イオンモール幕張",
  "amount": 6000
}
```

レスポンス例（200 OK）
```JSON
{
  "data": {
    "date": "2026-10-24",
    "type": "expense",
    "category_id": 1,
    "memo": "イオンモール幕張",
    "amount": 6000
  }
}
```

#### 収支明細の削除
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/household/transactions/1 |
| HTTPメソッド | DELETE |
| 概要 | 指定した収支明細データの削除を行う | 

※ 削除に成功した場合は`204 No Content`を返す

#### 家計簿支出カテゴリ一覧取得
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/household/categories?type=expense |
| HTTPメソッド | GET |
| 概要 | DBに登録されている家計簿の支出カテゴリを取得する（家計簿登録画面のカテゴリ欄で使用する） | 

レスポンス例（200 OK） 
```JSON
{
  "type": "expense",
  "data": [
    {
      "id": 1,
      "name": "食費",
      "icon": "utensils"
    },
    {
      "id": 2,
      "name": "日用品",
      "icon": "shopping-bag"
    },
    {
      "id": 3,
      "name": "衣服",
      "icon": "shirt"
    },
    {
      "id": 4,
      "name": "美容",
      "icon": "scissors"
    }
  ]
}
```

#### 家計簿収入カテゴリ一覧取得
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/household/categories?type=income |
| HTTPメソッド | GET |
| 概要 | DBに登録されている家計簿の収入カテゴリを取得する（家計簿登録画面のカテゴリ欄で使用する） | 

レスポンス例（200 OK）
```JSON
{
  "type": "income",
  "data": [
    {
      "id": 14,
      "name": "給料",
      "icon": "briefcase"
    },
    {
      "id": 15,
      "name": "おこづかい",
      "icon": "wallet"
    },
    {
      "id": 16,
      "name": "賞与",
      "icon": "gift"
    },
    {
      "id": 17,
      "name": "その他",
      "icon": "piggy-bank"
    }
  ]
}
```

#### 積立シミュレーション（過去実績）
| 項目 | 内容 |
| ---- | ---- |
| URLパス | /api/investment/risk-simulations |
| HTTPメソッド | POST |
| 概要 | 過去の指定した期間と積立投資額を入力すると、毎月の価額の変動と最終的な運用結果を取得する |

リクエスト例
```JSON
{
  "start_month": "2010-01",
  "end_month": "2020-12",
  "investment_index": "sp500",
  "monthly_amount": 50000
}
```

レスポンス例（200 OK）
```JSON
{
  "data": {
    "summary": {
      "principal": 6000000,
      "asset_value": 7203837,
      "profit": 1203837,
      "return_rate": 20.06
    },
    "chart": [
      {
        "month": "2010-01",
        "principal": 50000,
        "asset_value": 50230
      },
      {
        "month": "2010-02",
        "principal": 100000,
        "asset_value": 101250
      },
      {
        "month": "2010-03",
        "principal": 150000,
        "asset_value": 153200
      }
    ],
    "conditions": {
      "investment_index": "sp500",
      "start_month": "2010-01",
      "end_month": "2020-12",
      "monthly_amount": 50000,
      "contribution_months": 132
    }
  }
}
```

## 5. UI・UX設計
※ デザインの参考になりそうなサイト  
[keeps](https://keeps.homes/)  
[BelleClub](https://www.belleclub.jp/)  
[pal-system](https://www.pal-system.co.jp/koeteku/okome/)  
[ソコスト](https://soco-st.com/category/money) 

## 6. その他
### 積立シミュレーション（過去実績）について
積立投資の仕組みについて少し学習が必要。具体的には以下の内容で調査が必要。

- 総資産の算出方法について
指定した年月間で毎月積立を行った場合の、最終的な資産額の算出の仕方  
→ ここは毎月の購入口数を計算、積立終了日の保有口数と価格をかければOK

- 為替込みのシミュレーションについて
為替相場も考慮したときどのような演算が必要になるかを調べる。  
→ 当時の為替レートからドルに変換したのち口数を算出すればOK

- 複利について
NISAでは配当金は受け取ることはできず、再投資される形で運用がなされる。この処理の演算方法を調べる。  
→ S&P500（Total Return）という配当再投資込み指数がある。こちらを使用すれば特別な演算は不要なよう。  
[yahoo finance S&P500（Total Return）](https://finance.yahoo.com/quote/%5ESP500TR/?guccounter=1&guce_referrer=aHR0cHM6Ly93d3cuZ29vZ2xlLmNvbS8&guce_referrer_sig=AQAAAB2RRD5xKrm5wDC1MGdsMI5UBxy1tBw1SGzFacAaooVYQM_L5zj7SaYBRRSmc09d7P3TQwjdP_8nG3faoC4cx4c19lgGTKIHZIBPfHA9p4mWCYyp9hFr-zeKXZduE7fdVV0vrgOaDLs7N3RjJQoAcX3YR6k2W4A2PXrjPaLO9-5r)

※ 為替換算は[Frankfurter API](https://frankfurter.dev/ja/?utm_source=chatgpt.com)を使用する  
