<?php
/**
 * Cấu hình cơ bản cho WordPress
 *
 * Trong quá trình cài đặt, file "wp-config.php" sẽ được tạo dựa trên nội dung 
 * mẫu của file này. Bạn không bắt buộc phải sử dụng giao diện web để cài đặt, 
 * chỉ cần lưu file này lại với tên "wp-config.php" và điền các thông tin cần thiết.
 *
 * File này chứa các thiết lập sau:
 *
 * * Thiết lập MySQL
 * * Các khóa bí mật
 * * Tiền tố cho các bảng database
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Thiết lập MySQL - Bạn có thể lấy các thông tin này từ host/server ** //
/** Tên database MySQL */
define( 'DB_NAME', 'id14352418_bangiay' );

/** Username của database */
define( 'DB_USER', 'id14352418_root' );

/** Mật khẩu của database */
define( 'DB_PASSWORD', 'trantieucuc@98L' );

/** Hostname của database */
define( 'DB_HOST', 'localhost' );

/** Database charset sử dụng để tạo bảng database. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Kiểu database collate. Đừng thay đổi nếu không hiểu rõ. */
define('DB_COLLATE', '');

/**#@+
 * Khóa xác thực và salt.
 *
 * Thay đổi các giá trị dưới đây thành các khóa không trùng nhau!
 * Bạn có thể tạo ra các khóa này bằng công cụ
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Bạn có thể thay đổi chúng bất cứ lúc nào để vô hiệu hóa tất cả
 * các cookie hiện có. Điều này sẽ buộc tất cả người dùng phải đăng nhập lại.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Xv+uxqj(<yt#j.K$26e#kp|/1yAl-vqA4cZm//<HGj4cuOdtZ0aocto*>V~mLXI ' );
define( 'SECURE_AUTH_KEY',  '~QOa~/8}~WEE>O2q~X*#&(+cPiY:k56EO=n.AoO/O0 NmmO>S~|VjdP@$_VPF1.?' );
define( 'LOGGED_IN_KEY',    'ej,DQpfuQ>&qk0_OrxiAV0/6+-PWGe4{u_&pe1{C(S1kn^KAS:!AnA%1vE*U^ROj' );
define( 'NONCE_KEY',        'Pn/%y;Ac#o^g^Q6B.|slb^O hLcI}Ds1*KWZeV{z,@!mc{m%#,gj!2A;AYEa 2ie' );
define( 'AUTH_SALT',        'rWW:oPiiq1]d6ZtN+dqY;|$qqUMHB3`eH6cz DR%b^(MP!|N21mVlvz+SfQeon45' );
define( 'SECURE_AUTH_SALT', 'X]v930|HoT_>HNC:N)WuvR0a&}7pV8wakaUxeaJMU<))7l62{+WU?j[r`[y`x@Q*' );
define( 'LOGGED_IN_SALT',   'PAb<JsxK()|DX^#v<?j&W.3@@D$J:Wg=BX,)l(`}p-q)0zXsCP}#g6Br]@Aw+y_K' );
define( 'NONCE_SALT',       'Fn7KzM|U2@wh@c$OJyRP/a86aDFV}5LqZ;w6!_Y@,JN6U7<u4;kjj|%k#%}<feUH' );

/**#@-*/

/**
 * Tiền tố cho bảng database.
 *
 * Đặt tiền tố cho bảng giúp bạn có thể cài nhiều site WordPress vào cùng một database.
 * Chỉ sử dụng số, ký tự và dấu gạch dưới!
 */
$table_prefix = 'wp_';

/**
 * Dành cho developer: Chế độ debug.
 *
 * Thay đổi hằng số này thành true sẽ làm hiện lên các thông báo trong quá trình phát triển.
 * Chúng tôi khuyến cáo các developer sử dụng WP_DEBUG trong quá trình phát triển plugin và theme.
 *
 * Để có thông tin về các hằng số khác có thể sử dụng khi debug, hãy xem tại Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Đó là tất cả thiết lập, ngưng sửa từ phần này trở xuống. Chúc bạn viết blog vui vẻ. */

/** Đường dẫn tuyệt đối đến thư mục cài đặt WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Thiết lập biến và include file. */
require_once(ABSPATH . 'wp-settings.php');
