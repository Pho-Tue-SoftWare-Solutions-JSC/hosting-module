# Hitechcloud HostBill Module

## 1. Giới thiệu

`Hitechcloud` là module `Hosting` cho **HostBill**, dùng để kết nối HostBill với **Hitechcloud Agent API** nhằm tự động cấp phát và quản lý dịch vụ shared hosting.

Module này được thiết kế để đặt trong thư mục HostBill:

- `includes/modules/Hosting/hitechcloud/`

> Lưu ý quan trọng: trong workspace hiện tại thư mục đang có tên `hitechcloud-module`, nhưng khi deploy vào HostBill nên đổi đúng tên thư mục runtime thành `hitechcloud` để khớp với code và định nghĩa route API.

---

## 2. Mục tiêu của module

Module cung cấp các khả năng chính:

- Kết nối đến Hitechcloud Agent API bằng `API URL` và `API Key`
- Tạo hosting account tự động từ HostBill
- Suspend / Unsuspend / Terminate account
- Đổi mật khẩu hosting account
- Đổi package / thay đổi giới hạn tài nguyên
- Mở rộng giao diện quản trị, giao diện client và REST API cho các tính năng hosting phổ biến
- Hỗ trợ cron sync usage và event lifecycle
- Hỗ trợ widget/toggle để bật tắt tính năng ở client area

---

## 3. Vị trí cài đặt đúng trong HostBill

### 3.1. Đường dẫn khuyến nghị

Copy toàn bộ module vào:

- `includes/modules/Hosting/hitechcloud/`

Cấu trúc sau khi đưa vào HostBill nên tương tự:

```text
includes/
└── modules/
    └── Hosting/
        └── hitechcloud/
            ├── class.hitechcloud.php
            ├── install.sql
            ├── translations.json
            ├── admin/
            ├── api/
            ├── cron/
            ├── event/
            ├── lib/
            ├── user/
            └── widgets/
```

### 3.2. Vì sao phải dùng đúng tên `hitechcloud`

Code hiện tại đang hard-code và suy luận nhiều đường dẫn theo tên module:

- API routes đăng ký file tại `includes/modules/Hosting/hitechcloud/api/class.hitechcloud_apiroutes.php`
- Admin/User controller dựng đường dẫn template theo `strtolower($this->module->getModuleName())`
- `getModuleName()` tương ứng với module `Hitechcloud` và sẽ được suy ra thành `hitechcloud`

Nếu đặt sai tên thư mục, HostBill có thể:

- không load đúng module
- không load đúng API routes
- không tìm thấy template
- không hiển thị đúng controller/widget

---

## 4. Tổng quan cấu trúc project

### 4.1. File lõi

#### `class.hitechcloud.php`
File chính của module.

Chức năng:

- khai báo metadata module
- định nghĩa server connection fields
- định nghĩa product options
- định nghĩa account details fields
- khởi tạo API client
- xử lý các action provisioning chính

### 4.2. Thư mục `lib/`

#### `lib/Constants.php`
Chứa constant dùng xuyên suốt module:

- trạng thái tài khoản
- key cho detail fields
- key cho product options

#### `lib/HiTechCloudAPI.php`
HTTP client dùng cURL để gọi Hitechcloud Agent API.

Hiện đang có các method lõi:

- `getHealth()`
- `createAccount()`
- `suspendAccount()`
- `unsuspendAccount()`
- `terminateAccount()`
- `changePassword()`
- `changePackage()`
- `getAccount()`
- `listAccounts()`

#### `lib/include.php`
Bootstrap include các file cần thiết trong `lib/`.

### 4.3. Thư mục `admin/`

#### `admin/class.hitechcloud_controller.php`
Controller phía admin.

Dự kiến xử lý:

- xem thông tin account
- xem thông tin server
- đồng bộ danh sách account từ remote server

### 4.4. Thư mục `user/`

#### `user/class.hitechcloud_controller.php`
Controller phía client area.

Dự kiến xử lý các tính năng:

- domains
- databases
- SSL
- PHP
- SFTP/FTP
- cron jobs
- backups
- stats
- logs

### 4.5. Thư mục `api/`

#### `api/class.hitechcloud_apiroutes.php`
Định nghĩa REST API routes cho service hosting.

#### `api/hitechcloud_apiroutes.json`
Metadata đăng ký route cho HostBill API.

### 4.6. Thư mục `cron/`

#### `cron/class.hitechcloud_controller.php`
Cron controller phục vụ:

- hourly sync bandwidth usage
- daily sync disk usage
- daily SSL renewal check

### 4.7. Thư mục `event/`

#### `event/class.hitechcloud_handle.php`
Event handler cho vòng đời account:

- account created
- account suspended
- account unsuspended
- account terminated
- password changed
- package changed

### 4.8. Thư mục `widgets/`

Chứa widget toggle cho client area feature:

- backup
- cronjob
- databases
- domains
- logs
- php
- sftp
- ssl
- stats

### 4.9. File khác

#### `translations.json`
Các chuỗi text chính của module.

#### `install.sql`
Hiện tại không tạo bảng custom nào.

---

## 5. Metadata module

Trong `class.hitechcloud.php`:

- Module name: `Hitechcloud`
- Description: `Hitechcloud Shared Hosting Management`
- Version: `1.0.0`

Module kế thừa `HostingModule` và implement interface `Constants`.

---

## 6. Cấu hình server trong HostBill

Module dùng các trường kết nối server sau:

- `field1` → `API URL`
- `field2` → `API Key`

Ngoài ra code vẫn đọc thêm:

- `host` / `ip`
- `username`
- `password`

Tuy nhiên phần API client hiện tại thực tế sử dụng chủ yếu:

- `API URL`
- `API Key`

### 6.1. Ý nghĩa các trường

#### API URL
Ví dụ:

- `https://server.example.com:8443`

Đây là base URL của Hitechcloud Agent API.

#### API Key
Được gửi qua header:

- `X-API-Key: <your-api-key>`

### 6.2. Cách module khởi tạo kết nối

Trong method `connect($connect)`:

- chuẩn hóa `api_url`
- lấy `api_key`
- lưu connection vào `$this->connection`
- khởi tạo `HitechcloudAPI`

### 6.3. Test kết nối

Method `testConnection()` sẽ gọi:

- `GET /api/v1/health`

Nếu API trả về bình thường, HostBill có thể xác nhận kết nối hoạt động.

---

## 7. Product options trong HostBill

Module định nghĩa các option cấu hình gói hosting:

- `Plan Name`
- `Disk Quota (MB)`
- `Bandwidth (MB)`
- `Max Addon Domains`
- `Max Databases`
- `Max FTP Accounts`
- `Max Cron Jobs`
- `PHP Version`
- `Shell Access`
- `SSL Enabled`
- `Backup Enabled`

### 7.1. Giá trị mặc định hiện tại

- `Plan Name` → `default`
- `Disk Quota (MB)` → `10240`
- `Bandwidth (MB)` → `102400`
- `Max Addon Domains` → `5`
- `Max Databases` → `5`
- `Max FTP Accounts` → `5`
- `Max Cron Jobs` → `3`
- `PHP Version` → danh sách mặc định `7.4`, `8.0`, `8.1`, `8.2`, `8.3`
- `Shell Access` → checkbox
- `SSL Enabled` → checkbox
- `Backup Enabled` → checkbox

### 7.2. Ghi chú về `PHP Version`

Cấu hình hiện tại đang để:

- `type = select`
- `default = ['7.4', '8.0', '8.1', '8.2', '8.3']`

Cách biểu diễn này có thể chưa hoàn toàn khớp format select option mà HostBill kỳ vọng. Trong code, nếu chưa có giá trị được chọn thì module sẽ lấy phần tử đầu tiên của mảng, tức `7.4`.

Khi triển khai thực tế, nên kiểm tra lại UI product config trong HostBill để xác nhận option này hiển thị đúng.

---

## 8. Account detail fields lưu trong HostBill

Module lưu thông tin account vào các field:

- `option1` → `username`
- `option2` → `password`
- `option3` → `domain`

Các field này được dùng khi:

- tạo account
- suspend / unsuspend / terminate
- đổi mật khẩu
- đổi package
- load thông tin account hiện tại

---

## 9. Luồng provisioning chính

## 9.1. `Create()`

Chức năng:

- kiểm tra trạng thái account hiện tại
- không cho provision lại nếu service đang `Active` hoặc `Suspended`
- build payload từ product options + account details
- gọi API tạo account
- cập nhật lại username nếu API trả về username mới

Điều kiện bắt buộc:

- phải có `username`
- phải có `domain`

Payload gửi đi gồm:

- `username`
- `password`
- `domain`
- `plan_name`
- `disk_quota`
- `bandwidth`
- `max_domains`
- `max_databases`
- `max_ftp`
- `max_cronjobs`
- `php_version`
- `shell_access`
- `ssl_enabled`
- `backup_enabled`

Endpoint sử dụng:

- `POST /api/v1/accounts`

## 9.2. `Suspend()`

Suspend account từ HostBill.

Endpoint:

- `POST /api/v1/accounts/{username}/suspend`

Reason mặc định:

- `Suspended by HostBill`

## 9.3. `Unsuspend()`

Endpoint:

- `POST /api/v1/accounts/{username}/unsuspend`

## 9.4. `Terminate()`

Endpoint:

- `DELETE /api/v1/accounts/{username}`

## 9.5. `ChangePassword($newpassword)`

Endpoint:

- `PUT /api/v1/accounts/{username}/password`

Sau khi đổi thành công, module cập nhật luôn password đã lưu tại HostBill.

## 9.6. `ChangePackage()`

Endpoint:

- `PUT /api/v1/accounts/{username}/package`

Dùng để upgrade/downgrade hoặc thay đổi quota và quyền của account.

---

## 10. API client nội bộ

`lib/HiTechCloudAPI.php` là lớp trung gian giữa HostBill và Hitechcloud Agent API.

### 10.1. Cơ chế request

- dùng cURL
- gửi header:
  - `X-API-Key`
  - `Accept: application/json`
  - `Content-Type: application/json`
- hỗ trợ `GET`, `POST`, `PUT`, `DELETE`
- parse JSON response
- ném exception khi:
  - lỗi cURL
  - response không phải JSON hợp lệ
  - HTTP status >= 400

### 10.2. Timeout mặc định

- `connectTimeout = 15s`
- `timeout = 30s`

### 10.3. Điểm cần chú ý về SSL

Hiện tại API client đang tắt verify SSL:

- `CURLOPT_SSL_VERIFYPEER => false`
- `CURLOPT_SSL_VERIFYHOST => 0`

Điều này có nghĩa:

- thuận tiện khi server dùng certificate self-signed
- nhưng giảm độ an toàn kết nối

Khuyến nghị production:

- bật verify SSL nếu hạ tầng cho phép
- dùng certificate hợp lệ
- chỉ giữ cấu hình hiện tại trong môi trường test nội bộ hoặc khi thực sự cần thiết

### 10.4. Yêu cầu với API response

Client hiện giả định **mọi response đều là JSON**, kể cả các thao tác `DELETE`.

Vì vậy Hitechcloud Agent API nên luôn trả body JSON hợp lệ, ví dụ:

```json
{"status":true}
```

Nếu API trả body rỗng hoặc plain text, client hiện tại sẽ báo lỗi parse JSON.

---

## 11. Admin controller

File: `admin/class.hitechcloud_controller.php`

### 11.1. `beforeCall()`

Thực hiện:

- load translation `hitechcloud`
- dựng đường dẫn thư mục template
- dựng URL cho template

### 11.2. `accountDetails($params)`

Dự kiến:

- lấy thông tin account từ model `Accounts`
- lấy `username`
- gọi API để lấy chi tiết account remote
- assign dữ liệu vào template

### 11.3. `serverInfo($params)`

Dự kiến:

- gọi API lấy thông tin server
- assign vào template

### 11.4. `syncAccounts($params)`

Dự kiến:

- gọi API lấy danh sách account từ server
- sync về HostBill

### 11.5. Trạng thái thực tế hiện tại

Phần `syncAccounts()` hiện mới chỉ:

- lấy danh sách account
- đếm số lượng
- chưa có logic reconcile local/remote thực sự

Tức là đây mới là khung chức năng, chưa hoàn thiện việc đồng bộ thật sự.

---

## 12. User controller

File: `user/class.hitechcloud_controller.php`

Controller này là nền tảng cho các tính năng trong client area.

### 12.1. Privileges hiện tại

Các cờ quyền đang được khai báo cứng:

- `o_domains`
- `o_databases`
- `o_ssl`
- `o_php`
- `o_sftp`
- `o_cronjob`
- `o_backup`
- `o_stats`
- `o_logs`

Mặc định đều đang là `true`.

### 12.2. Các chức năng dự kiến

#### Domains
- list domain
- add domain
- delete domain

#### Databases
- list database
- create database
- delete database

#### SSL
- list SSL
- issue Let's Encrypt
- install custom SSL

#### PHP
- xem thông tin PHP
- switch version
- update settings

#### SFTP
- list account
- create account
- delete account

#### Cron jobs
- list cron
- create cron
- delete cron

#### Backups
- list backup
- create backup
- restore backup
- delete backup

#### Stats
- xem usage stats

#### Logs
- xem access/error logs

### 12.3. Trạng thái thực tế hiện tại

Controller đã có luồng xử lý logic, nhưng nhiều method API mà controller gọi **chưa tồn tại trong `HiTechCloudAPI.php`**.

Điều này có nghĩa là:

- cấu trúc controller đã có
- nhưng nhiều chức năng client area chưa chạy được hoàn chỉnh nếu chưa bổ sung API client methods tương ứng

---

## 13. API routes cho service

File:

- `api/class.hitechcloud_apiroutes.php`
- `api/hitechcloud_apiroutes.json`

### 13.1. Các endpoint đã khai báo

#### Account
- `GET /service/@id/account`

#### Domains
- `GET /service/@id/domains`
- `POST /service/@id/domains`
- `DELETE /service/@id/domains/@domain`

#### Databases
- `GET /service/@id/databases`
- `POST /service/@id/databases`
- `DELETE /service/@id/databases/@db_name`

#### SSL
- `GET /service/@id/ssl`
- `POST /service/@id/ssl`

#### Stats
- `GET /service/@id/stats`

#### Backups
- `GET /service/@id/backups`
- `POST /service/@id/backups`
- `POST /service/@id/backups/@backup_id/restore`
- `DELETE /service/@id/backups/@backup_id`

#### Cronjobs
- `GET /service/@id/cronjobs`
- `POST /service/@id/cronjobs`
- `DELETE /service/@id/cronjobs/@cron_id`

#### PHP
- `GET /service/@id/php`
- `POST /service/@id/php/version`

#### SFTP
- `GET /service/@id/sftp`
- `POST /service/@id/sftp`
- `DELETE /service/@id/sftp/@ftp_user`

#### Logs
- `GET /service/@id/logs`

### 13.2. Ghi chú về auth

Trong file JSON:

- top-level `auth` đang là `false`
- nhưng phần lớn route con lại đặt `auth: true`

Nên test lại trên môi trường HostBill thực tế để xác định precedence của route-level auth.

---

## 14. Cron jobs

File: `cron/class.hitechcloud_controller.php`

### 14.1. `call_Hourly()`

Ý tưởng hiện tại:

- lấy toàn bộ active account dùng module `hitechcloud`
- gọi API lấy bandwidth usage
- cập nhật usage vào HostBill

### 14.2. `call_Daily()`

Ý tưởng hiện tại:

- lấy active account
- gọi API lấy disk usage
- update usage vào HostBill
- list SSL certificates
- tự động renew SSL nếu còn dưới 7 ngày hết hạn

### 14.3. Trạng thái thực tế hiện tại

Cron hiện phụ thuộc vào nhiều method chưa tồn tại, ví dụ:

#### Thiếu trong API client
- `getBandwidthUsage()`
- `getDiskUsage()`
- `listSSL()`
- `renewSSL()`

#### Thiếu trong main module
- `updateBandwidthUsage()`
- `updateDiskUsage()`

Vì vậy cron mới ở trạng thái định hướng/chưa hoàn chỉnh.

---

## 15. Event handler

File: `event/class.hitechcloud_handle.php`

Các event chính:

- `onAccountCreated()`
- `onAccountSuspended()`
- `onAccountUnsuspended()`
- `onAccountTerminated()`
- `onPasswordChanged()`
- `onPackageChanged()`

### 15.1. Ý nghĩa

Event handler dùng để chạy tác vụ phụ sau các sự kiện vòng đời account, ví dụ:

- post-create setup
- post-terminate cleanup
- debug logging

### 15.2. Trạng thái thực tế hiện tại

Hiện code có gọi thêm các method như:

- `postCreate()`
- `postTerminate()`

Nhưng các method này chưa có trong `HiTechCloudAPI.php`.

Do đó phần event hiện mới là khung mở rộng, chưa hoàn tất.

---

## 16. Widgets và feature toggle

### 16.1. Base widget

File: `widgets/class.th_toggle.php`

Base widget kế thừa `HostingWidget` và đóng vai trò widget toggle feature.

### 16.2. Các widget hiện có

- `widget_thw_backup`
- `widget_thw_cronjob`
- `widget_thw_databases`
- `widget_thw_domains`
- `widget_thw_logs`
- `widget_thw_php`
- `widget_thw_sftp`
- `widget_thw_ssl`
- `widget_thw_stats`

### 16.3. Cấu hình từng widget

#### Backup
- `create`
- `restore`
- `delete`

#### Cronjob
- `create`
- `delete`

#### Databases
- `create`
- `delete`

#### Domains
- `addon_domains`
- `subdomains`

#### Logs
- `access_log`
- `error_log`

#### PHP
- `version_switch`
- `settings`

#### SFTP
- `create`
- `delete`

#### SSL
- `auto_ssl`
- `custom_ssl`

#### Stats
- `disk`
- `bandwidth`
- `cpu`

### 16.4. Ghi chú thực tế

Mặc dù widget đã định nghĩa các sub-option khá chi tiết, phần `user controller` hiện mới kiểm soát theo nhóm lớn bằng các cờ hard-coded. Nghĩa là:

- toggle theo nhóm tính năng có định hướng
- nhưng chưa thấy logic áp dụng đầy đủ từng sub-option widget vào action thực tế

---

## 17. Translation strings

File: `translations.json`

Chứa các message cơ bản như:

- account create success/error
- suspend success/error
- unsuspend success/error
- terminate success/error
- password change success/error
- package change success/error
- connection test success/error
- các label của product option

Có thể mở rộng file này nếu cần đa ngôn ngữ hoặc chuẩn hóa wording cho UI.

---

## 18. Cài đặt thực tế từng bước

## Bước 1: Copy module vào HostBill

Đưa source vào đúng thư mục:

- `includes/modules/Hosting/hitechcloud/`

Đảm bảo file chính tồn tại:

- `includes/modules/Hosting/hitechcloud/class.hitechcloud.php`

## Bước 2: Kiểm tra quyền đọc file

Web server/PHP process phải có quyền đọc toàn bộ thư mục module.

## Bước 3: Kiểm tra PHP extensions

Server chạy HostBill cần có:

- PHP cURL extension
- JSON support
- SSL/TLS support

## Bước 4: Tạo server trong HostBill

Trong phần server/module của HostBill, cấu hình:

- module: `Hitechcloud`
- `API URL`
- `API Key`

## Bước 5: Tạo product hosting

Gán product với module `Hitechcloud`, sau đó cấu hình các option:

- Plan Name
- Disk Quota
- Bandwidth
- Max Domains
- Max Databases
- Max FTP
- Max Cron Jobs
- PHP Version
- Shell Access
- SSL Enabled
- Backup Enabled

## Bước 6: Tạo service test

Tạo một đơn hàng/service test với:

- username
- password
- domain

## Bước 7: Test connection

Thử dùng chức năng test connection của HostBill để xác minh endpoint:

- `GET /api/v1/health`

## Bước 8: Test provisioning

Thử các action cơ bản theo thứ tự:

1. `Create`
2. `Suspend`
3. `Unsuspend`
4. `ChangePassword`
5. `ChangePackage`
6. `Terminate`

Đây là nhóm action hiện gần với mức hoàn chỉnh nhất của codebase.

---

## 19. Checklist kiểm thử sau cài đặt

### 19.1. Kết nối API
- [ ] API URL chính xác
- [ ] API Key hợp lệ
- [ ] `/api/v1/health` trả JSON hợp lệ
- [ ] timeout phù hợp

### 19.2. Provisioning
- [ ] tạo account thành công
- [ ] username được lưu lại đúng trong HostBill
- [ ] suspend hoạt động
- [ ] unsuspend hoạt động
- [ ] terminate hoạt động
- [ ] đổi password hoạt động
- [ ] đổi package hoạt động

### 19.3. Client area / Admin area
- [ ] controller load không lỗi
- [ ] route API đăng ký đúng
- [ ] widget hiển thị đúng
- [ ] translation load đúng

### 19.4. Bảo mật
- [ ] cân nhắc bật SSL verify ở production
- [ ] API key được lưu an toàn
- [ ] không expose debug output ngoài ý muốn

---

## 20. Các giới hạn/điểm chưa hoàn thiện rất quan trọng

Đây là phần cần lưu ý nhất khi bàn giao hoặc triển khai thực tế.

### 20.1. Nhiều method được gọi nhưng chưa được implement trong `HiTechCloudAPI.php`

Hiện API client mới có các method provisioning lõi. Tuy nhiên các controller/cron/event đang gọi thêm rất nhiều method chưa tồn tại, ví dụ:

- `getAccountInfo()`
- `getServerInfo()`
- `addDomain()`
- `deleteDomain()`
- `listDomains()`
- `createDatabase()`
- `deleteDatabase()`
- `listDatabases()`
- `issueSSL()`
- `installCustomSSL()`
- `listSSL()`
- `renewSSL()`
- `switchPHPVersion()`
- `updatePHPSettings()`
- `getPHPInfo()`
- `createSFTPAccount()`
- `deleteSFTPAccount()`
- `listSFTPAccounts()`
- `createCronJob()`
- `deleteCronJob()`
- `listCronJobs()`
- `createBackup()`
- `restoreBackup()`
- `deleteBackup()`
- `listBackups()`
- `getUsageStats()`
- `getLogs()`
- `getBandwidthUsage()`
- `getDiskUsage()`
- `postCreate()`
- `postTerminate()`

Hệ quả:

- nhiều tính năng đã được dựng khung ở controller/API route/cron/event
- nhưng sẽ chưa hoạt động nếu chưa bổ sung method tương ứng vào API client

### 20.2. Vấn đề visibility của helper methods

Trong `class.hitechcloud.php`, các method:

- `getApi()`
- `getUsername()`

đang được khai báo là `protected`.

Tuy nhiên chúng lại được gọi từ:

- admin controller
- user controller
- api routes
- event handler

Theo visibility chuẩn của PHP, điều này là không hợp lệ nếu không có proxy/special mechanism từ framework.

Khuyến nghị rà soát:

- đổi thành `public` nếu đúng mục đích dùng ngoài class
- hoặc bổ sung wrapper public phù hợp

### 20.3. Chưa có thư mục `templates/`

Cả admin và user controller đều giả định tồn tại thư mục:

- `templates/`

Nhưng trong source hiện tại chưa thấy thư mục này.

Hệ quả:

- giao diện render có thể chưa hoàn chỉnh
- controller có thể assign dữ liệu nhưng không có file view để hiển thị

### 20.4. Sync account admin chưa hoàn tất

`syncAccounts()` hiện mới chỉ load danh sách account và đếm số lượng.

Chưa có:

- map account remote ↔ local
- create/update trạng thái local
- reconcile quota/usage
- xử lý account bị lệch trạng thái

### 20.5. Cron chưa chạy hoàn chỉnh

Cron đang gọi các method chưa có trong module/API client, vì vậy chưa thể hoạt động đầy đủ.

### 20.6. Event post-action chưa đầy đủ

Phần hậu xử lý create/terminate cũng đang phụ thuộc các method chưa có.

---

## 21. Khuyến nghị phát triển tiếp

Để module đạt mức production-ready, nên làm tiếp các việc sau:

### Ưu tiên 1: Hoàn thiện `HiTechCloudAPI.php`
Bổ sung đầy đủ method mà các controller/cron/event đang gọi.

### Ưu tiên 2: Sửa visibility
Mở public cho helper nào cần truy cập từ controller/api/event.

### Ưu tiên 3: Bổ sung `templates/`
Tạo view cho:

- admin account details
- server info
- client area domains/databases/ssl/php/sftp/backups/stats/logs

### Ưu tiên 4: Hoàn thiện sync logic
Viết logic reconcile thật sự cho admin sync.

### Ưu tiên 5: Hoàn thiện cron usage sync
Bổ sung update usage vào HostBill theo đúng API/framework của HostBill.

### Ưu tiên 6: Siết bảo mật SSL
Bật verify SSL cho production nếu API server có cert hợp lệ.

---

## 22. Đánh giá tình trạng hiện tại

Ở trạng thái hiện tại, module có thể xem là:

- **đã có phần khung tốt cho một module HostBill chuẩn**
- **đã có provisioning lõi**
- **đã có định hướng mở rộng admin/client/API/cron/event/widget khá rõ**
- nhưng **chưa hoàn thiện toàn bộ tính năng đã khai báo**

Nói ngắn gọn:

- phần `core provisioning` gần hoàn chỉnh hơn
- phần `advanced management features` vẫn còn ở mức scaffold / draft implementation

---

## 23. Tóm tắt nhanh cho người triển khai

Nếu cần triển khai nhanh, hãy nhớ 5 điểm sau:

1. Đặt module đúng đường dẫn: `includes/modules/Hosting/hitechcloud/`
2. Cấu hình đúng `API URL` và `API Key`
3. Test trước các action lõi: create / suspend / unsuspend / change password / change package / terminate
4. Đừng kỳ vọng toàn bộ client/admin/API/cron feature đã chạy đủ ngay, vì nhiều method API chưa được implement
5. Nên hoàn thiện thêm `HiTechCloudAPI.php`, `templates/`, và visibility của helper methods trước khi đưa production

---

## 24. Thông tin source hiện tại

Các file chính đã được đọc để viết tài liệu này:

- `class.hitechcloud.php`
- `install.sql`
- `lib/Constants.php`
- `lib/HiTechCloudAPI.php`
- `lib/include.php`
- `admin/class.hitechcloud_controller.php`
- `user/class.hitechcloud_controller.php`
- `api/class.hitechcloud_apiroutes.php`
- `api/hitechcloud_apiroutes.json`
- `cron/class.hitechcloud_controller.php`
- `event/class.hitechcloud_handle.php`
- `widgets/class.th_toggle.php`
- toàn bộ widget con trong `widgets/thw_*`
- `translations.json`

---

## 25. Kết luận

`Hitechcloud` là một module HostBill có kiến trúc khá đầy đủ cho bài toán quản lý shared hosting qua API. Module đã có nền tảng tốt cho provisioning và đã dựng sẵn nhiều điểm mở rộng quan trọng. Tuy nhiên, để sử dụng ổn định trong production, vẫn cần hoàn thiện thêm phần API client, template, cron sync và logic cho các tính năng nâng cao.

Nếu dùng để demo, POC hoặc phát triển tiếp nội bộ, codebase hiện tại là nền tảng tốt. Nếu dùng production ngay, nên rà soát và hoàn thiện các mục ở phần **Các giới hạn/điểm chưa hoàn thiện** trước.

---

**Deploy path nhắc lại:**

- `includes/modules/Hosting/hitechcloud/`

**File docs này:**

- `README.md`
