# 設計
このセクションでは以下の項目について検討を行い、開発アプリの設計を行う。

1. 画面遷移図・ワイヤフレーム
2. DB設計・テーブル定義書
3. システム設計
4. HTTPエンドポイント
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
<img width="533" height="334" alt="image" src="https://github.com/user-attachments/assets/e1bff194-58eb-44a1-9077-a43aca47825f" />

### テーブル設計

#### usersテーブル
| カラム名        | データ型     | NULL | キー    | 初期値             | AUTO INCREMENT |
| ------------     | ------------ | ---- | ------- | ----------------- | -------------- |
| id               | INTEGER      |      | PRIMARY |                   | YES            |
| program_id       | INTEGER      |      |         |                   |                |
| season_number    | INTEGER      | YES  |         |                   |                |
| episode_number   | INTEGER      | YES  |         |                   |                |
| title            | VARCHAR(255) | YES  |         |                   |                |
| description      | TEXT         | YES  | YES     |                   |                |
| video_time       | TIME         |      |         |                   |                |
| release_date     | DATE         |      |         | CURRENT_TIMESTAMP |                |

## 5. UI・UX設計
※ デザインの参考になりそうなサイト  
[keeps](https://keeps.homes/)  
[BelleClub](https://www.belleclub.jp/)  
[pal-system](https://www.pal-system.co.jp/koeteku/okome/)  
[ソコスト](https://soco-st.com/category/money) 
