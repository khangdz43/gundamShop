# Mô tả chức năng hệ thống Gundam Store (phiên bản siêu chi tiết)

## 1. Mục đích của hệ thống

Gundam Store là một website bán hàng mô hình Gundam (Gunpla) chạy trên nền tảng PHP + MySQL, được thiết kế để phục vụ đầy đủ quy trình từ khách hàng tìm sản phẩm, thêm vào giỏ hàng, đặt hàng, theo dõi đơn hàng cho đến việc quản trị sản phẩm, đơn hàng, người dùng và gửi thông báo. Hệ thống còn tích hợp chatbot AI bằng Google Gemini để hỗ trợ khách hàng và AI phân tích kinh doanh cho quản trị viên.

Nói ngắn gọn, đây là một hệ thống thương mại điện tử mini nhưng có đủ các thành phần quan trọng của một cửa hàng bán hàng online thực tế.

---

## 2. Tổng quan về cách hệ thống hoạt động

Hệ thống được chia thành 3 nhóm chính:

1. Phía khách hàng
   - Người dùng có thể xem sản phẩm, tìm kiếm, chọn mua, tạo đơn hàng và theo dõi trạng thái đơn hàng.

2. Phía nhân viên / quản trị
   - Nhân viên và admin có thể thao tác với đơn hàng, đổi trả, thông báo và dữ liệu bán hàng.

3. Hệ thống hỗ trợ
   - Có chức năng xác thực đăng nhập, phân quyền, lưu giỏ hàng, áp dụng mã giảm giá, gửi thông báo và tích hợp AI.

---

## 3. Các vai trò người dùng

### 3.1. Khách hàng (customer)
Đây là vai trò phổ biến nhất. Người dùng có thể:
- đăng ký tài khoản
- đăng nhập/đăng xuất
- xem danh sách sản phẩm
- xem chi tiết sản phẩm
- thêm vào giỏ hàng
- đặt hàng
- xem lịch sử đơn hàng
- yêu cầu đổi/ trả hàng
- sử dụng chatbot AI để hỏi về sản phẩm

### 3.2. Nhân viên (employee)
Nhân viên có thể truy cập khu vực quản trị nhưng chỉ trong phạm vi chức năng được phân quyền. Ví dụ:
- xem đơn hàng
- cập nhật trạng thái đơn hàng
- xử lý đổi trả
- xem thông báo

### 3.3. Quản trị viên (admin)
Admin có toàn quyền hệ thống, bao gồm:
- quản lý sản phẩm
- quản lý danh mục
- quản lý người dùng
- quản lý đơn hàng
- quản lý mã giảm giá
- gửi thông báo
- xem AI phân tích kinh doanh

---

## 4. Phân chia chức năng theo module

## 4.1. Module trang chủ
Trang chủ là điểm vào đầu tiên của website. Nó có nhiệm vụ:
- hiển thị banner quảng cáo
- hiển thị danh mục sản phẩm chính
- hiển thị sản phẩm sale
- hiển thị sản phẩm nổi bật
- hiển thị thống kê nhanh về số lượng sản phẩm và khách hàng
- dẫn người dùng đến trang sản phẩm hoặc trang đăng nhập

Trên trang chủ, người dùng có thể:
- click vào danh mục để xem sản phẩm theo loại
- click vào sản phẩm để xem chi tiết
- thêm sản phẩm vào giỏ hàng ngay từ homepage

## 4.2. Module sản phẩm
Đây là module quan trọng nhất của hệ thống bán hàng.

### Chức năng chính
- hiển thị danh sách sản phẩm
- phân trang sản phẩm
- tìm kiếm sản phẩm theo tên, mô tả, series, danh mục
- lọc theo loại như HG, MG, RG, PG, SD, MGEX
- lọc theo giá
- sắp xếp theo giá tăng/giảm, tên, mới nhất, phổ biến
- hiển thị sản phẩm sale và sản phẩm nổi bật

### Mục tiêu
Giúp khách hàng dễ dàng tìm thấy đúng sản phẩm mình cần trong một kho hàng có thể khá lớn.

### Luồng người dùng
1. Người dùng mở trang sản phẩm
2. Hệ thống lấy sản phẩm đang active từ database
3. Người dùng dùng bộ lọc hoặc tìm kiếm
4. Hệ thống trả về danh sách phù hợp
5. Người dùng chọn một sản phẩm để xem chi tiết

## 4.3. Module chi tiết sản phẩm
Trang chi tiết sản phẩm cho phép người dùng xem đầy đủ thông tin của một sản phẩm cụ thể.

### Nội dung hiển thị
- tên sản phẩm
- ảnh sản phẩm
- giá bán
- giá cũ nếu đang sale
- loại sản phẩm (HG/MG/...)
- mô tả sản phẩm
- số lượng tồn kho
- tình trạng sản phẩm

### Chức năng liên quan
- thêm sản phẩm vào giỏ hàng
- xem thông tin đầy đủ trước khi quyết định mua

---

## 5. Module giỏ hàng
Giỏ hàng là nơi lưu các sản phẩm người dùng đã chọn nhưng chưa thanh toán.

### Chức năng chính
- thêm sản phẩm vào giỏ hàng
- tăng/giảm số lượng sản phẩm
- xóa sản phẩm khỏi giỏ hàng
- chọn sản phẩm để thanh toán (có thể chọn một vài mục trong giỏ)
- tính tổng tiền
- tính phí vận chuyển
- áp dụng mã giảm giá
- xóa toàn bộ giỏ hàng

### Logic nghiệp vụ
- mỗi user có một giỏ hàng riêng
- sản phẩm trong giỏ hàng được liên kết với user_id
- số lượng không được vượt quá tồn kho
- nếu sản phẩm hết hàng thì hệ thống không cho mua tiếp

### Mục tiêu
Giúp người dùng tập hợp sản phẩm trước khi thanh toán và kiểm soát giá tiền trước khi đặt hàng.

---

## 6. Module thanh toán và đặt hàng
Đây là module chuyển đổi hành vi mua hàng từ “chọn sản phẩm” sang “tạo đơn hàng thật”.

### Quy trình
1. Người dùng chọn sản phẩm từ giỏ hàng
2. Hệ thống kiểm tra tồn kho
3. Người dùng nhập thông tin giao hàng
4. Người dùng chọn phương thức thanh toán
5. Người dùng có thể nhập mã giảm giá
6. Hệ thống tính lại tổng tiền
7. Nếu hợp lệ, hệ thống tạo đơn hàng mới
8. Hệ thống giảm stock của sản phẩm
9. Xóa sản phẩm khỏi giỏ hàng
10. Chuyển hướng sang trang đặt hàng thành công

### Thông tin cần lưu khi tạo đơn hàng
- mã đơn hàng
- thông tin khách hàng
- địa chỉ giao hàng
- số điện thoại
- email
- phương thức thanh toán
- tổng tiền trước giảm giá
- phí vận chuyển
- số tiền giảm giá
- tổng tiền cuối cùng
- trạng thái đơn hàng

### Các trạng thái đơn hàng thường gặp
- pending: chờ xác nhận
- processing: đang xử lý
- shipped: đã giao cho đơn vị vận chuyển
- completed: hoàn thành
- cancelled: đã hủy

### Mục tiêu
Đảm bảo đơn hàng được tạo đúng, dữ liệu lưu đầy đủ và tồn kho bị trừ đúng.

---

## 7. Module quản lý đơn hàng của khách hàng
Sau khi đã đặt hàng, khách hàng có thể xem lại các đơn hàng đã mình tạo.

### Chức năng chính
- xem danh sách đơn hàng
- lọc theo tháng hoặc trạng thái
- tìm kiếm theo mã đơn hàng
- xem chi tiết một đơn hàng
- xem tổng chi tiêu
- xem biểu đồ chi tiêu theo tháng

### Mục tiêu
Giúp khách hàng dễ dàng theo dõi trạng thái và lịch sử mua hàng.

---

## 8. Module đổi trả
Hệ thống hỗ trợ yêu cầu đổi trả cho đơn hàng đã mua.

### Chức năng chính
- khách hàng gửi yêu cầu đổi trả
- nhân viên / admin xem yêu cầu đổi trả
- cập nhật trạng thái xử lý

### Ý nghĩa
Đây là một phần rất quan trọng trong thương mại điện tử vì khách hàng có thể gặp lỗi sản phẩm, sai mẫu, hoặc cần đổi trả do không phù hợp.

---

## 9. Module tài khoản và hồ sơ người dùng
Người dùng có thể quản lý hồ sơ cá nhân.

### Chức năng
- đăng ký tài khoản
- đăng nhập bằng username hoặc email
- ghi nhớ đăng nhập bằng cookie
- đăng xuất
- cập nhật thông tin cá nhân
- đổi mật khẩu

### Ý nghĩa
Hệ thống cần có dữ liệu khách hàng để liên kết giỏ hàng, đơn hàng và lịch sử mua sắm.

---

## 10. Module mã giảm giá
Mã giảm giá là công cụ kích thích mua hàng.

### Chức năng
- khách hàng nhập mã giảm giá khi thanh toán
- hệ thống kiểm tra tính hợp lệ
- hệ thống kiểm tra điều kiện áp dụng như tổng đơn hàng tối thiểu
- nếu hợp lệ thì giảm giá trị đơn hàng
- nếu mã đã dùng quá số lượt cho phép thì bị vô hiệu hóa

### Mục tiêu
Tăng doanh số và tạo chiến dịch khuyến mãi.

---

## 11. Module chatbot AI
Hệ thống tích hợp chatbot AI bằng Gemini để hỗ trợ khách hàng và quản trị viên.

### 11.1. Chatbot cho khách hàng
Khách hàng có thể hỏi về:
- sản phẩm nào phù hợp
- giá và khuyến mãi
- cách đặt hàng
- quy trình thanh toán
- đổi trả
- vận chuyển

### 11.2. AI cho admin
Admin có thể dùng AI để phân tích dữ liệu bán hàng như:
- doanh thu
- sản phẩm bán chạy
- đơn hàng chờ xử lý
- xu hướng mua hàng
- gợi ý chiến lược kinh doanh

### Cách hoạt động
1. Người dùng gửi câu hỏi từ giao diện
2. Hệ thống gọi API Gemini
3. Gemini trả lời kết quả
4. Hệ thống hiển thị câu trả lời cho người dùng

---

## 12. Module quản trị admin
Đây là phần điều hành hệ thống cho quản trị viên.

### 12.1. Dashboard
Dashboard là trung tâm điều hành, nơi admin có thể nhìn thấy:
- tổng số đơn hàng
- tổng số sản phẩm đang bán
- tổng số khách hàng
- số đơn hàng chờ xử lý
- doanh thu
- thống kê theo thời gian

### 12.2. Quản lý sản phẩm
Admin có thể:
- thêm sản phẩm mới
- chỉnh sửa thông tin sản phẩm
- xóa hoặc ẩn sản phẩm
- gán sản phẩm vào danh mục
- cập nhật giá, stock, sale, featured
- quản lý hình ảnh sản phẩm

### 12.3. Quản lý danh mục
Admin có thể:
- tạo và quản lý danh mục sản phẩm
- liên kết sản phẩm với danh mục phù hợp
- giúp giao diện hiển thị đúng cấu trúc sản phẩm

### 12.4. Quản lý đơn hàng
Admin có thể:
- xem toàn bộ đơn hàng trong hệ thống
- lọc theo trạng thái và thời gian
- cập nhật trạng thái đơn hàng
- xem chi tiết từng đơn hàng
- kiểm tra thông tin khách hàng và sản phẩm trong đơn

### 12.5. Quản lý người dùng
Admin có thể:
- xem danh sách người dùng
- thêm tài khoản nhân viên
- xem vai trò của từng người dùng
- kích hoạt hoặc khóa tài khoản

### 12.6. Quản lý mã giảm giá
Admin có thể:
- tạo mã giảm giá
- kích hoạt / vô hiệu hóa mã
- thiết lập điều kiện áp dụng
- kiểm soát số lần sử dụng

### 12.7. Gửi thông báo
Admin có thể gửi thông báo tới người dùng hoặc toàn hệ thống để thông báo về:
- khuyến mãi
- thay đổi trạng thái đơn hàng
- cập nhật hệ thống

---

## 13. Module quản lý của các vai trò phụ trợ
Hệ thống phân chia rõ ràng hai vai trò phụ trợ để tránh việc một người có thể thao tác sang module không thuộc phạm vi của mình.

### 13.1. Module quản lý của người quản lý đơn hàng
Vai trò này chỉ được phép làm việc với các chức năng liên quan đến đơn hàng.

#### Chức năng có thể dùng
- xem danh sách đơn hàng
- tìm kiếm và lọc đơn hàng
- xem chi tiết từng đơn hàng
- cập nhật trạng thái đơn hàng
- xem thông báo liên quan đến đơn hàng

#### Không được phép
- quản lý đổi trả
- chỉnh sửa sản phẩm
- quản lý người dùng
- quản lý mã giảm giá

### 13.2. Module quản lý của người quản lý đổi trả
Vai trò này chỉ được phép làm việc với các yêu cầu đổi trả.

#### Chức năng có thể dùng
- xem danh sách yêu cầu đổi trả
- xem chi tiết từng yêu cầu đổi trả
- cập nhật trạng thái xử lý đổi trả
- xem thông báo liên quan đến đổi trả

#### Không được phép
- quản lý đơn hàng
- chỉnh sửa sản phẩm
- quản lý người dùng
- quản lý mã giảm giá

### Điểm khác biệt so với admin
Cả hai vai trò này đều không có quyền toàn diện như admin, và mỗi vai trò chỉ được phép thao tác trong phạm vi chức năng riêng của mình.

---

## 14. Bảo mật và phân quyền
Hệ thống có phân quyền cơ bản để kiểm soát ai được nhìn thấy và thao tác gì.

### Các nguyên tắc
- khách hàng chỉ được vào các chức năng liên quan đến mua hàng và hồ sơ cá nhân
- người quản lý đơn hàng chỉ được phép xử lý nghiệp vụ đơn hàng
- người quản lý đổi trả chỉ được phép xử lý nghiệp vụ đổi trả
- admin có toàn quyền hệ thống

### Cơ chế thực hiện
- session để lưu trạng thái đăng nhập
- role và position để phân quyền
- một số trang bị chặn bằng middleware-style check như requireLogin, requireEmployee, requireAdmin

---

## 15. Cấu trúc thư mục và vai trò từng phần

- [public](public): các trang hiển thị cho khách hàng như trang chủ, sản phẩm, giỏ hàng, checkout, đơn hàng, đăng nhập
- [admin](admin): các trang quản trị cho admin và nhân viên
- [api](api): các endpoint xử lý AJAX và logic backend như giỏ hàng, chatbot, validate coupon, notification
- [includes](includes): các file dùng chung như header, footer, auth, chatbot widget, ngôn ngữ
- [config](config): cấu hình database và Gemini
- [assets](assets): file CSS, JS, hình ảnh

### Ý nghĩa kiến trúc này
- public dùng cho giao diện người dùng
- admin dùng cho giao diện quản trị
- api dùng cho các hành động động như AJAX
- includes dùng cho tái sử dụng code

---

## 16. Các bảng dữ liệu chính trong hệ thống

### 16.1. users
Lưu thông tin người dùng, bao gồm:
- id
- username
- email
- password
- role
- position
- full_name
- phone
- address
- is_active

### 16.2. products
Lưu thông tin sản phẩm:
- id
- name
- price
- old_price
- image
- description
- stock
- grade
- category_id
- status
- is_sale
- is_featured

### 16.3. categories
Lưu danh mục sản phẩm để phân loại sản phẩm.

### 16.4. cart
Lưu giỏ hàng của user. Mỗi dòng là một sản phẩm đang được user chọn mua.

### 16.5. orders
Lưu thông tin đơn hàng chính.

### 16.6. order_items
Lưu các sản phẩm nằm trong từng đơn hàng.

### 16.7. coupons
Lưu mã giảm giá.

### 16.8. notifications và notification_users
Lưu thông báo hệ thống và ai gửi cho user nào.

---

## 17. Luồng người dùng hoàn chỉnh từ đầu đến cuối

### Luồng 1: Khách truy cập xem sản phẩm
1. Người dùng mở trang chủ
2. Hệ thống hiển thị banner và sản phẩm nổi bật
3. Người dùng chọn vào danh mục hoặc vào trang sản phẩm
4. Hệ thống lọc và hiển thị danh sách phù hợp
5. Người dùng chọn sản phẩm xem chi tiết

### Luồng 2: Người dùng mua hàng
1. Người dùng đăng nhập hoặc đăng ký
2. Thêm sản phẩm vào giỏ hàng
3. Chọn sản phẩm cần thanh toán
4. Nhập thông tin giao hàng
5. Áp dụng mã giảm giá (nếu có)
6. Tạo đơn hàng
7. Hệ thống giảm stock và xóa khỏi giỏ hàng
8. Chuyển hướng sang trang thành công

### Luồng 3: Khách xem lịch sử đơn hàng
1. Người dùng đăng nhập
2. Vào trang lịch sử đơn hàng
3. Hệ thống lấy toàn bộ đơn hàng của user đó
4. Người dùng xem trạng thái và chi tiết

### Luồng 4: Admin quản lý hệ thống
1. Admin đăng nhập vào trang quản trị
2. Vào dashboard để xem thống kê
3. Quản lý sản phẩm, đơn hàng, người dùng hoặc thông báo
4. Hệ thống lưu thay đổi vào database

---

## 18. Các tính năng nổi bật của hệ thống

- bán hàng trực tuyến đầy đủ
- phân quyền rõ ràng theo vai trò
- tích hợp AI chatbot
- hỗ trợ mã giảm giá
- hỗ trợ đổi trả
- hỗ trợ đa ngôn ngữ
- có thống kê và dashboard cho admin
- có thông báo hệ thống
- có cấu hình môi trường để dễ deploy

---

## 19. Điểm cần ghi nhớ khi đọc code

Nếu muốn hiểu toàn bộ website nhanh, hãy đọc theo thứ tự sau:

1. [includes/auth.php](includes/auth.php) – hiểu hệ thống đăng nhập và phân quyền
2. [public/index.php](public/index.php) – hiểu homepage
3. [public/product/list.php](public/product/list.php) – hiểu danh sách sản phẩm và bộ lọc
4. [public/cart.php](public/cart.php) – hiểu giỏ hàng
5. [public/checkout.php](public/checkout.php) – hiểu quy trình đặt hàng
6. [public/order/history.php](public/order/history.php) – hiểu lịch sử đơn hàng
7. [admin/index.php](admin/index.php) – hiểu dashboard admin
8. [admin/orders.php](admin/orders.php) – hiểu quản lý đơn hàng
9. [api/chat.php](api/chat.php) và [api/admin_ai_chat.php](api/admin_ai_chat.php) – hiểu AI
10. [config/db.php](config/db.php) và [config/gemini.php](config/gemini.php) – hiểu cấu hình hệ thống

---

## 20. Kết luận

Gundam Store là một web bán hàng mini nhưng có cấu trúc khá đầy đủ cho một hệ thống thương mại điện tử cơ bản. Nếu đọc đúng các file chính theo trình tự trên, người đọc sẽ hiểu được toàn bộ hành trình từ khách hàng tìm sản phẩm, thêm vào giỏ, đặt hàng, theo dõi đơn hàng cho đến việc quản trị và sử dụng AI để hỗ trợ kinh doanh.
